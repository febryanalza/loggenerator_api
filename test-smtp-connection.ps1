# === NETWORK DIAGNOSTIC SCRIPT FOR SMTP CONNECTION ===
Write-Host "=== 1. DNS Resolution Test ===" -ForegroundColor Cyan
nslookup smtp-relay.brevo.com

Write-Host "`n=== 2. Ping Test ===" -ForegroundColor Cyan
Test-Connection smtp-relay.brevo.com -Count 2 -ErrorAction SilentlyContinue

Write-Host "`n=== 3. TCP Port 587 Test ===" -ForegroundColor Cyan
Test-NetConnection smtp-relay.brevo.com -Port 587 -InformationLevel Detailed

Write-Host "`n=== 4. Alternative Ports Test ===" -ForegroundColor Cyan
Write-Host "Testing port 465 (SSL/TLS)..."
Test-NetConnection smtp-relay.brevo.com -Port 465

Write-Host "`nTesting port 25 (Plain SMTP)..."
Test-NetConnection smtp-relay.brevo.com -Port 25

Write-Host "`n=== DIAGNOSTIC COMPLETE ===" -ForegroundColor Green
