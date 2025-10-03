=== POSTMAN TESTING FOR LOGBOOK UPDATE ===

📋 REQUEST DETAILS:
   Method: PUT
   URL: http://localhost:8000/api/logbook-entries/{entry_id}
   Headers: 
   - Authorization: Bearer {your_token}
   - Content-Type: application/json
   - Accept: application/json

🔧 BODY (raw JSON):
{
  "data": {
    "Nama Kegiatan": "Rapat koordinasi dengan supervisor",
    "Tanggal": "2025-10-04", 
    "Jam Mulai": "09:00",
    "Jam Selesai": "10:30",
    "Lokasi": "Ruang Meeting Lt. 2",
    "Deskripsi": "Membahas progress project dan kendala yang dihadapi",
    "Jumlah Peserta": "5",
    "Status": "Selesai"
  }
}

📝 SHORTER VERSION (Minimal):
{
  "data": {
    "Nama Kegiatan": "Meeting singkat",
    "Tanggal": "2025-10-04",
    "Jam": "10:00",
    "Lokasi": "Online"
  }
}

🧪 TEST SCENARIOS:

1. SUCCESSFUL UPDATE (200):
   - User: Editor/Owner role
   - Entry: Exists in database
   - Data: All required fields

2. AUTHORIZATION FAIL (403):
   - User: No Editor/Owner role
   - Response: "Only users with Owner or Editor role..."

3. VALIDATION FAIL (422):
   - Missing required fields
   - Response: "Missing required fields"

4. NOT FOUND (404):
   - Invalid entry_id
   - Response: "Logbook entry not found"

⚡ QUICK TESTING STEPS:
1. Login to get token
2. Get logbook entry ID from /logbook-entries
3. Use entry ID in URL
4. Send PUT request with JSON body
5. Check response

🎯 EXPECTED SUCCESS RESPONSE:
{
  "success": true,
  "message": "Logbook entry updated successfully",
  "data": {
    "id": "entry_id",
    "template_id": "template_id",
    "data": {...updated_data...},
    "updated_at": "2025-10-04T..."
  }
}