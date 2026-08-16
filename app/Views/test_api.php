<!DOCTYPE html>
<html>

<head>
    <title>Test QR Scan API</title>
</head>

<body>
    <h1>Test QR Code API</h1>

    <h2>Test 1: Form Data (application/x-www-form-urlencoded)</h2>
    <form id="form1">
        <input type="text" id="nis1" placeholder="NIS" value="12345">
        <button type="button" onclick="testFormData()">Test Form Data</button>
    </form>
    <div id="result1"></div>

    <h2>Test 2: JSON Data</h2>
    <form id="form2">
        <input type="text" id="nis2" placeholder="NIS" value="12345">
        <button type="button" onclick="testJSON()">Test JSON</button>
    </form>
    <div id="result2"></div>

    <h2>Test 3: Get Parameter</h2>
    <form id="form3">
        <input type="text" id="nis3" placeholder="NIS" value="12345">
        <button type="button" onclick="testGET()">Test GET</button>
    </form>
    <div id="result3"></div>

    <hr>
    <h2>Console Output</h2>
    <pre id="console"></pre>

    <script>
        function log(msg) {
            console.log(msg);
            document.getElementById('console').textContent += msg + '\n';
        }

        function testFormData() {
            const nis = document.getElementById('nis1').value;
            const formData = new FormData();
            formData.append('nis', nis);

            log('=== Test 1: Form Data ===');
            log('NIS: ' + nis);
            log('Content-Type: application/x-www-form-urlencoded (implicit)');

            fetch('<?= base_url('/api/qrcode/scan') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(d => {
                    log('Response: ' + JSON.stringify(d));
                    document.getElementById('result1').textContent = JSON.stringify(d, null, 2);
                })
                .catch(e => {
                    log('Error: ' + e);
                    document.getElementById('result1').textContent = 'Error: ' + e;
                });
        }

        function testJSON() {
            const nis = document.getElementById('nis2').value;

            log('=== Test 2: JSON ===');
            log('NIS: ' + nis);
            log('Content-Type: application/json');

            fetch('<?= base_url('/api/qrcode/scan') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        nis: nis
                    })
                })
                .then(r => r.json())
                .then(d => {
                    log('Response: ' + JSON.stringify(d));
                    document.getElementById('result2').textContent = JSON.stringify(d, null, 2);
                })
                .catch(e => {
                    log('Error: ' + e);
                    document.getElementById('result2').textContent = 'Error: ' + e;
                });
        }

        function testGET() {
            const nis = document.getElementById('nis3').value;

            log('=== Test 3: GET Parameter ===');
            log('NIS: ' + nis);
            log('URL: api/qrcode/scan?nis=' + nis);

            fetch('<?= base_url('/api/qrcode/scan') ?>?nis=' + encodeURIComponent(nis), {
                    method: 'POST'
                })
                .then(r => r.json())
                .then(d => {
                    log('Response: ' + JSON.stringify(d));
                    document.getElementById('result3').textContent = JSON.stringify(d, null, 2);
                })
                .catch(e => {
                    log('Error: ' + e);
                    document.getElementById('result3').textContent = 'Error: ' + e;
                });
        }

        log('Test page loaded at: <?= base_url() ?>');
        log('API endpoint: <?= base_url('/api/qrcode/scan') ?>');
    </script>
</body>

</html>