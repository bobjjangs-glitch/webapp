<?php
$action = $_GET['action'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

function adminJson(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── 충전 승인 ─────────────────────────────────────────────────
if ($action === 'confirm_charge') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) adminJson(['error' => 'ID 없음'], 400);

    $req = DB::fetchOne("SELECT * FROM charge_requests WHERE id = ? AND status = 'pending'", [$id]);
    if (!$req) adminJson(['error' => '대기 중인 요청이 없습니다.'], 404);

    $pdo = DB::connect();
    $pdo->beginTransaction();
    try {
        // 잔액 증가 또는 최초 생성
        $existing = DB::fetchOne("SELECT id, balance FROM credit_balance WHERE user_id = ?", [$req['user_id']]);
        $totalAmount = (int)$req['amount'] + (int)($req['bonus_amount'] ?? 0);

        if ($existing) {
            $newBalance = $existing['balance'] + $totalAmount;
            DB::execute("UPDATE credit_balance SET balance = ?, total_charged = total_charged + ? WHERE user_id = ?",
                [$newBalance, $totalAmount, $req['user_id']]);
        } else {
            $newBalance = $totalAmount;
            DB::execute("INSERT INTO credit_balance (user_id, balance, total_charged) VALUES (?, ?, ?)",
                [$req['user_id'], $newBalance, $totalAmount]);
        }

        // 거래 내역 추가
        DB::execute("INSERT INTO credit_transactions (user_id, type, amount, balance_after, description, payment_method, status)
                     VALUES (?, 'charge', ?, ?, ?, ?, 'completed')",
            [$req['user_id'], $totalAmount, $newBalance, '충전 승인: '.number_format($req['amount']).'원', $req['payment_method']]);

        // 요청 상태 업데이트
        DB::execute("UPDATE charge_requests SET status = 'confirmed', confirmed_at = NOW() WHERE id = ?", [$id]);

        // 사용자 알림
        DB::execute("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'success')",
            [$req['user_id'], '💰 충전 완료!', number_format($totalAmount).'원이 충전되었습니다. 잔액: '.number_format($newBalance).'원']);

        $pdo->commit();
        adminJson(['success' => true, 'new_balance' => $newBalance]);
    } catch(Exception $e) {
        $pdo->rollBack();
        adminJson(['error' => $e->getMessage()], 500);
    }
}

// ── 충전 취소 ─────────────────────────────────────────────────
if ($action === 'cancel_charge') {
    $id   = (int)($body['id']   ?? 0);
    $memo = trim($body['memo']  ?? '관리자 취소');
    if (!$id) adminJson(['error' => 'ID 없음'], 400);

    DB::execute("UPDATE charge_requests SET status = 'cancelled', admin_memo = ? WHERE id = ? AND status = 'pending'",
        [$memo, $id]);
    adminJson(['success' => true]);
}

// ── 대기 전체 승인 ─────────────────────────────────────────────
if ($action === 'confirm_all_charges') {
    $pending = DB::fetchAll("SELECT * FROM charge_requests WHERE status = 'pending'");
    if (empty($pending)) adminJson(['success' => true, 'count' => 0]);

    $pdo = DB::connect();
    $pdo->beginTransaction();
    $count = 0;
    try {
        foreach ($pending as $req) {
            $totalAmount = (int)$req['amount'] + (int)($req['bonus_amount'] ?? 0);
            $existing    = DB::fetchOne("SELECT balance FROM credit_balance WHERE user_id = ?", [$req['user_id']]);
            if ($existing) {
                $newBalance = $existing['balance'] + $totalAmount;
                DB::execute("UPDATE credit_balance SET balance = ?, total_charged = total_charged + ? WHERE user_id = ?", [$newBalance, $totalAmount, $req['user_id']]);
            } else {
                $newBalance = $totalAmount;
                DB::execute("INSERT INTO credit_balance (user_id, balance, total_charged) VALUES (?, ?, ?)", [$req['user_id'], $newBalance, $totalAmount]);
            }
            DB::execute("INSERT INTO credit_transactions (user_id, type, amount, balance_after, description, payment_method, status) VALUES (?, 'charge', ?, ?, ?, ?, 'completed')",
                [$req['user_id'], $totalAmount, $newBalance, '충전 승인: '.number_format($req['amount']).'원', $req['payment_method']]);
            DB::execute("UPDATE charge_requests SET status='confirmed', confirmed_at=NOW() WHERE id=?", [$req['id']]);
            DB::execute("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'success')",
                [$req['user_id'], '💰 충전 완료!', number_format($totalAmount).'원이 충전되었습니다.']);
            $count++;
        }
        $pdo->commit();
        adminJson(['success' => true, 'count' => $count]);
    } catch(Exception $e) {
        $pdo->rollBack();
        adminJson(['error' => $e->getMessage()], 500);
    }
}

// ── 수동 크레딧 지급 ──────────────────────────────────────────
if ($action === 'grant_credit') {
    $email  = trim($body['email']  ?? '');
    $amount = (int)($body['amount'] ?? 0);
    $memo   = trim($body['memo']   ?? '관리자 수동 지급');
    if (!$email || $amount < 100) adminJson(['error' => '이메일과 금액(최소 100원)을 입력해주세요.'], 400);

    $user = DB::fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
    if (!$user) adminJson(['error' => '해당 이메일의 회원이 없습니다.'], 404);

    $existing = DB::fetchOne("SELECT balance FROM credit_balance WHERE user_id = ?", [$user['id']]);
    if ($existing) {
        $newBalance = $existing['balance'] + $amount;
        DB::execute("UPDATE credit_balance SET balance = ?, total_charged = total_charged + ? WHERE user_id = ?", [$newBalance, $amount, $user['id']]);
    } else {
        $newBalance = $amount;
        DB::execute("INSERT INTO credit_balance (user_id, balance, total_charged) VALUES (?, ?, ?)", [$user['id'], $newBalance, $amount]);
    }
    DB::execute("INSERT INTO credit_transactions (user_id, type, amount, balance_after, description, status) VALUES (?, 'admin', ?, ?, ?, 'completed')",
        [$user['id'], $amount, $newBalance, $memo]);
    DB::execute("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'success')",
        [$user['id'], '💰 크레딧 지급', number_format($amount).'원이 지급되었습니다. ('.htmlspecialchars($memo).')']);

    adminJson(['success' => true, 'new_balance' => $newBalance]);
}

// ── 플랜 변경 ─────────────────────────────────────────────────
if ($action === 'change_plan') {
    $userId = (int)($body['user_id'] ?? 0);
    $plan   = trim($body['plan'] ?? '');
    $valid  = ['free','basic','premium','enterprise'];
    if (!$userId || !in_array($plan, $valid)) adminJson(['error' => '잘못된 요청'], 400);

    DB::execute("UPDATE users SET plan = ? WHERE id = ?", [$plan, $userId]);
    adminJson(['success' => true]);
}

// ── 회원 활성/정지 ────────────────────────────────────────────
if ($action === 'toggle_user') {
    $userId   = (int)($body['user_id']   ?? 0);
    $isActive = (int)($body['is_active'] ?? 0);
    if (!$userId) adminJson(['error' => 'ID 없음'], 400);

    DB::execute("UPDATE users SET is_active = ? WHERE id = ?", [$isActive, $userId]);
    adminJson(['success' => true]);
}

adminJson(['error' => '알 수 없는 액션: ' . $action], 400);
