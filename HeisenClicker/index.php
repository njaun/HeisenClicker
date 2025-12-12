<?php
session_start();


if (!isset($_SESSION['cryst'])) {
    $_SESSION['cryst'] = 0;
    $_SESSION['perSecond'] = 0;
    $_SESSION['clickValue'] = 1;
    $_SESSION['upgradeCost'] = 10;
    $_SESSION['clickUpgradeCost'] = 15;
    $_SESSION['level'] = 0;
    $_SESSION['tier'] = 'Chilli P';
    $_SESSION['upgradeCount'] = 0;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'upgrade' && $_SESSION['cryst'] >= $_SESSION['upgradeCost']) {
            $_SESSION['cryst'] -= $_SESSION['upgradeCost'];
            $_SESSION['perSecond']++;
            $_SESSION['upgradeCount']++;
            $_SESSION['upgradeCost'] = max(1, floor($_SESSION['upgradeCost'] * 1.5));
            
            if ($_SESSION['upgradeCount'] % 2 === 0) {
                $_SESSION['level']++;
            }
        } elseif ($_POST['action'] === 'clickUpgrade' && $_SESSION['cryst'] >= $_SESSION['clickUpgradeCost']) {
            $_SESSION['cryst'] -= $_SESSION['clickUpgradeCost'];
            $_SESSION['clickValue'] *= 1.50;
            $_SESSION['upgradeCount']++;
            $_SESSION['clickUpgradeCost'] = max(1, floor($_SESSION['clickUpgradeCost'] * 1.75));
            
            if ($_SESSION['upgradeCount'] % 2 === 0) {
                $_SESSION['level']++;
            }
        } elseif ($_POST['action'] === 'click') {
            $_SESSION['cryst'] += $_SESSION['clickValue'];
        } elseif ($_POST['action'] === 'autoincrement') {
            $_SESSION['cryst'] += $_SESSION['perSecond'];
        } elseif ($_POST['action'] === 'reset') {
            $_SESSION['cryst'] = 0;
            $_SESSION['perSecond'] = 0;
            $_SESSION['clickValue'] = 1;
            $_SESSION['upgradeCost'] = 10;
            $_SESSION['clickUpgradeCost'] = 15;
            $_SESSION['level'] = 0;
            $_SESSION['tier'] = 'Chilli P';
            $_SESSION['upgradeCount'] = 0;
        }
    }
}

if ($_SESSION['level'] >= 100) {
    $_SESSION['tier'] = 'Peak Stuff';
} elseif ($_SESSION['level'] >= 60) {
    $_SESSION['tier'] = 'Blue Stuff';
} elseif ($_SESSION['level'] >= 20) {
    $_SESSION['tier'] = 'Regular';
} elseif ($_SESSION['level'] >= 1) {
    $_SESSION['tier'] = 'Chilli P';
} else {
    $_SESSION['tier'] = 'Chilli P';
}
    
if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'cryst' => floor($_SESSION['cryst']),
        'level' => $_SESSION['level'],
        'upgradeCost' => $_SESSION['upgradeCost'],
        'clickUpgradeCost' => $_SESSION['clickUpgradeCost'],
        'perSecond' => $_SESSION['perSecond'],
        'tier' => $_SESSION['tier']
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>HeisenClicker</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container"> 
    <div class="upgradeContainer">
      <button id="upgradeBtn">Upgrade Auto Clicker: <?php echo $_SESSION['upgradeCost']; ?></button>
      <button id="clickUpgradeBtn">Upgrade Click Value: <?php echo $_SESSION['clickUpgradeCost']; ?></button>
      <button id="cheatBtn">Skip to next tier</button>
    </div>
    <div class="divider"><img src="divider.png" alt="divider"></div>
     
    <div class="cookieContainer">
      <img id="cookie" src="chillip.png" alt="Cookie" draggable="false">
    </div>
    <div class="divider"><img src="divider.png" alt="divider"></div>
    
    <div class="statsContainer">
      <div id="cryst">Cryst: <?php echo floor($_SESSION['cryst']); ?></div>
      <div id="level">Level: <?php echo $_SESSION['level']; ?></div>
      <div id="tier">Tier: <?php echo $_SESSION['tier']; ?></div>
    </div>
    
  </div>

  <script>
  const tierImages = {
    'Chilli P': 'chillip.png',
    'Regular': 'regular.png',
    'Blue Stuff': 'blue.png',
    'Peak Stuff': 'peak.png'
  };

  document.addEventListener('DOMContentLoaded', function () {
    const cookieEl = document.getElementById('cookie');
    const crystDisplay = document.getElementById('cryst');
    const levelDisplay = document.getElementById('level');
    const tierDisplay = document.getElementById('tier');
    const upgradeBtn = document.getElementById('upgradeBtn');
    const clickUpgradeBtn = document.getElementById('clickUpgradeBtn');
    const cheatBtn = document.getElementById('cheatBtn');

    if (!cookieEl) return;

    function updateUI() {
      fetch('<?php echo $_SERVER['PHP_SELF']; ?>?json=1')
        .then(response => response.json())
        .then(data => {
          crystDisplay.textContent = `Cryst: ${data.cryst}`;
          levelDisplay.textContent = `Level: ${data.level}`;
          tierDisplay.textContent = `Tier: ${data.tier}`;
          upgradeBtn.textContent = `Upgrade auto clicker: ${data.upgradeCost}`;
          clickUpgradeBtn.textContent = `Upgrade click value: ${data.clickUpgradeCost}`;
          upgradeBtn.disabled = data.cryst < data.upgradeCost;
          clickUpgradeBtn.disabled = data.cryst < data.clickUpgradeCost;
          
          if (tierImages[data.tier]) {
            cookieEl.src = tierImages[data.tier];
          }
        });
    }

    cookieEl.addEventListener('click', function () {
      cookieEl.classList.remove('animate');
      void cookieEl.offsetWidth;
      cookieEl.classList.add('animate');

      const formData = new FormData();
      formData.append('action', 'click');

      fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
        method: 'POST',
        body: formData
      }).then(() => updateUI());
    });

    cookieEl.addEventListener('animationend', function () {
      cookieEl.classList.remove('animate');
    });

    upgradeBtn.addEventListener('click', () => {
      const formData = new FormData();
      formData.append('action', 'upgrade');

      fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
        method: 'POST',
        body: formData
      }).then(() => updateUI());
    });

    clickUpgradeBtn.addEventListener('click', () => {
      const formData = new FormData();
      formData.append('action', 'clickUpgrade');

      fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
        method: 'POST',
        body: formData
      }).then(() => updateUI());
    });

    cheatBtn.addEventListener('click', () => {
      const formData = new FormData();
      formData.append('action', 'reset');
      alert('You wish.  Have fun starting over goober.');

      fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
        method: 'POST',
        body: formData
      }).then(() => updateUI());
    });

    setInterval(() => {
      fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
        method: 'POST',
        body: new URLSearchParams('action=autoincrement')
      }).then(() => updateUI());
    }, 1000);

    updateUI();
  });
  </script>
</body>
</html>