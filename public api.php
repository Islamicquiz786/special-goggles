<?php

if (!isset($_GET['number'])) {
    http_response_code(400);
    echo "Number parameter is required.";
    exit;
}

$number = $_GET['number'];

$url = 'https://paksim.pro/';
$postData = http_build_query(['number' => $number]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Host: paksim.pro",
    "content-type: application/x-www-form-urlencoded",
    "origin: https://paksim.pro",
    "user-agent: Mozilla/5.0 (Linux; Android 11; IN2019 Build/RP1A.201005.001) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.7204.67 Mobile Safari/537.36",
    "referer: https://paksim.pro/",
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "Error fetching data: " . curl_error($ch);
    curl_close($ch);
    exit;
}
curl_close($ch);

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($response);
libxml_clear_errors();

$xpath = new DOMXPath($dom);

$responseDiv = $xpath->query('//div[contains(@class, "response")]')->item(0);

if (!$responseDiv) {
    $content = "❌ No data found for this number.";
} else {
    $data = [];

    $rows = $xpath->query('.//table[@class="results-table"]//tr', $responseDiv);
    foreach ($rows as $row) {
        $key = trim($row->getElementsByTagName('th')->item(0)->textContent);
        $value = trim($row->getElementsByTagName('td')->item(0)->textContent);
        $data[$key] = $value;
    }

    $numbers = [];
    $liItems = $xpath->query('.//ul/li', $responseDiv);
    foreach ($liItems as $li) {
        $numbers[] = trim($li->textContent);
    }

    $data['Associated Numbers'] = $numbers;

    $emojiMap = [
        'Name' => '👤 Name',
        'CNIC' => '🆔 CNIC',
        'Address' => '🗺️ Address',
        'Associated Numbers' => '🔢 Associated Numbers',
    ];

    $content = "📞 SIM Information Result\n\n";
    foreach ($data as $key => $value) {
        $label = $emojiMap[$key] ?? $key;
        if (is_array($value)) {
            $content .= "$label:\n";
            foreach ($value as $v) {
                $content .= "   ➤ $v\n";
            }
        } else {
            $content .= "$label: $value\n";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FindSIMInfo Result</title>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body{background:#000;font-family:'Courier New',monospace;color:#0f0;margin:0;overflow:hidden}
    canvas{position:fixed;top:0;left:0;width:100%;height:100%;z-index:-1}
    .result-box{background:rgba(0,0,0,.8);padding:20px;margin:20px auto;width:80%;max-width:800px;
      box-shadow:0 0 20px red,0 0 20px blue,0 0 20px green;border-radius:10px;
      animation:glowing-border 3s infinite alternate, glowing-text 2s infinite alternate;
      white-space:pre-wrap;color:#fff;height:400px;overflow-y:auto}
    .copy-btn{margin-top:20px;padding:10px 20px;background:#0f0;color:#000;border:none;
      border-radius:5px;cursor:pointer;animation:glowing-border 2s infinite alternate}
    .copy-btn:hover{background:#0c0}
    @keyframes glowing-border{0%{border-color:red}50%{border-color:blue}100%{border-color:green}}
    @keyframes glowing-text  {0%{color:#f00}50%{color:#0f0}100%{color:#00f}}
    .social-btn{position:fixed;bottom:20px;width:60px;height:60px;border-radius:50%;
      background:#0f0;display:flex;align-items:center;justify-content:center;
      font-size:28px;color:#000;box-shadow:0 0 15px #0f0;z-index:999;
      animation:spin 4s linear infinite,glow 1.5s ease-in-out infinite alternate}
    .whatsapp{left:20px}.telegram{right:20px}
    @keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
    @keyframes glow{0%{box-shadow:0 0 10px #0f0}100%{box-shadow:0 0 40px #0f0}}

    /* Developer credit */
    .credit{position:fixed;bottom:100px;width:100%;text-align:center;color:#0f0;
      font-size:18px;text-shadow:0 0 10px #0f0,0 0 20px #0f0;animation:glowing-text 2s infinite alternate}
  </style>
</head>
<body>
<canvas id="matrixCanvas"></canvas>
<a href="https://wa.me/923221857442" target="_blank"
   class="social-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
<a href="https://t.me/Officialinnocent_51214" target="_blank"
   class="social-btn telegram"><i class="fab fa-telegram-plane"></i></a>

<div class="result-box" id="result-box"><?= nl2br(htmlspecialchars($content)) ?></div>
<button class="copy-btn" onclick="copyContent()">🫀</button>

<div class="credit">👨‍💻 Developer: Innocent</div>

<script>
function copyContent(){
  const t=document.createElement("textarea");
  t.value=document.getElementById("result-box").innerText;
  document.body.appendChild(t);t.select();document.execCommand("copy");
  t.remove();alert("All data copied to clipboard!");
}
const canvas=document.getElementById('matrixCanvas'),
      ctx=canvas.getContext('2d');
function resize(){canvas.width=innerWidth;canvas.height=innerHeight}
window.addEventListener('resize',resize);resize();
const letters=Array(256).join("1").split("");
setInterval(()=>{ctx.fillStyle="rgba(0,0,0,0.05)";
  ctx.fillRect(0,0,canvas.width,canvas.height);
  ctx.fillStyle="#0F0";
  letters.map((y_pos,index)=>{
    const text=String.fromCharCode(0x30A0+Math.random()*33),
          x_pos=index*10;
    ctx.fillText(text,x_pos,y_pos);
    letters[index]=y_pos>758+Math.random()*1e4?0:y_pos+10;
  });
},33);
</script>
</body>
</html>