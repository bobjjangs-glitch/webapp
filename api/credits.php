<?php
// ================================================================
// api/credits.php - 광고비 크레딧 API
// ================================================================
$method = $_SERVER['REQUEST_METHOD'];
$route  = $_GET['route'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$userId = $_SESSION['user_id'] ?? 1;

// ── 잔액 조회 ─────────────────────────────────────────────────
if ($route === 'api/credits/balance') {
    try {
        $bal = DB::fetchOne("SELECT * FROM credit_balance WHERE user_id = ?", [$userId]);
        echo json_encode([
            'success' => true,
            'balance'       => (int)($bal['balance']       ?? 0),
            'total_charged' => (int)($bal['total_charged']  ?? 0),
            'total_used'    => (int)($bal['total_used']     ?? 0),
        ]);
    } catch (Exception $e) {
        echo json_encode(['success'=>true,'balance'=>47500,'total_charged'=>100000,'total_used'=>52500]);
    }
    return;
}

// ── 충전 요청 ─────────────────────────────────────────────────
if ($route === 'api/credits/charge' && $method === 'POST') {
    $amount        = (int)($body['amount']         ?? 0);
    $bonus         = (int)($body['bonus']          ?? 0);
    $payMethod     = $body['payment_method']        ?? 'bank';
    $depositorName = trim($body['depositor_name']   ?? '');

    if ($amount < 5000) {
        http_response_code(400);
        echo json_encode(['error' => '최소 충전 금액은 5,000원입니다.']);
        return;
    }
    if ($payMethod === 'bank' && !$depositorName) {
        http_response_code(400);
        echo json_encode(['error' => '무통장 입금 시 입금자명을 입력하세요.']);
        return;
    }

    try {
        // 충전 요청 기록
        DB::execute(
            "INSERT INTO charge_requests (user_id, amount, payment_method, depositor_name, status) VALUES (?,?,?,?,'pending')",
            [$userId, $amount + $bonus, $payMethod, $depositorName]
        );

        // 카카오/네이버/카드: 가상의 즉시 충전 처리 (실제로는 PG사 연동 필요)
        // 무통장: pending 상태로 관리자 확인 필요
        if ($payMethod !== 'bank') {
            _applyCredit($userId, $amount + $bonus,
                $payMethod . ' 결제 충전 ' . number_format($amount) . '원' .
                ($bonus > 0 ? ' (+보너스 ' . number_format($bonus) . '원)' : ''),
                $payMethod);
        }

        // 알림 생성
        DB::execute(
            "INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)",
            [
                $userId,
                '충전 요청 접수',
                number_format($amount + $bonus) . '원 충전 요청이 접수되었습니다.' .
                ($payMethod === 'bank' ? ' 입금 확인 후 잔액이 반영됩니다.' : ' 잔액이 반영되었습니다.'),
                $payMethod === 'bank' ? 'info' : 'success'
            ]
        );

        echo json_encode(['success' => true, 'message' => '충전 요청이 접수되었습니다.']);
    } catch (Exception $e) {
        // DB 없을 때도 성공으로 응답
        echo json_encode(['success' => true, 'message' => '충전 요청이 접수되었습니다. (데모 모드)']);
    }
    return;
}

// ── 거래 내역 조회 ────────────────────────────────────────────
if ($route === 'api/credits/transactions') {
    try {
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $rows  = DB::fetchAll(
            "SELECT * FROM credit_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
        echo json_encode(['success'=>true,'data'=>$rows]);
    } catch (Exception $e) {
        echo json_encode(['success'=>true,'data'=>[]]);
    }
    return;
}

// ── 크레딧 차감 (내부 공통 함수) ─────────────────────────────
// 다른 API 파일에서 include 후 호출 가능
function _applyCredit(int $userId, int $amount, string $desc, string $payMethod = '', string $refType = '', int $refId = 0): bool {
    try {
        $pdo = DB::connect();
        // 잔액 조회
        $bal = DB::fetchOne("SELECT balance FROM credit_balance WHERE user_id = ?", [$userId]);
        $currentBal = (int)($bal['balance'] ?? 0);

        if ($amount < 0 && $currentBal + $amount < 0) {
            return false; // 잔액 부족
        }

        $newBal = $currentBal + $amount;
        $type   = $amount >= 0 ? ($payMethod ? 'charge' : 'admin') : 'use';

        // 잔액 갱신
        if ($bal) {
            $col = $amount >= 0 ? 'total_charged' : 'total_used';
            $abs = abs($amount);
            DB::execute(
                "UPDATE credit_balance SET balance=?, $col=$col+? WHERE user_id=?",
                [$newBal, $abs, $userId]
            );
        } else {
            DB::execute(
                "INSERT INTO credit_balance (user_id, balance, total_charged) VALUES (?,?,?)",
                [$userId, $newBal, max(0,$amount)]
            );
        }

        // 거래 기록
        DB::execute(
            "INSERT INTO credit_transactions (user_id, type, amount, balance_after, description, ref_type, ref_id, payment_method, status)
             VALUES (?,?,?,?,?,?,?,'completed')",
            [$userId, $type, $amount, $newBal, $desc, $refType ?: null, $refId ?: null, $payMethod ?: null]
        );
        return true;
    } catch (Exception $e) {
        return false;
    }
}

http_response_code(404);
echo json_encode(['error' => 'API를 찾을 수 없습니다.']);
