#!/bin/bash
set -e
echo "🔨 빌드 시작..."
npm run build
echo "📂 dist 동기화..."
echo '{"version":1,"include":["/api/*"],"exclude":[]}' > dist/_routes.json
cp -r *.html dist/ 2>/dev/null || true
cp -r admin dist/ 2>/dev/null || true
cp -r js dist/ 2>/dev/null || true
cp -r css dist/ 2>/dev/null || true
cp -r data dist/ 2>/dev/null || true
cp -r components dist/ 2>/dev/null || true
echo "✅ 완료"
