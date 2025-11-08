<?php
// GitHub Copilot
session_start();

$options = ["rock", "paper", "scissors"];
$emojis = ["rock"=>"✊", "paper"=>"✋", "scissors"=>"✌️"];

// initialize session game state
if (!isset($_SESSION['player_score'])) {
    $_SESSION['player_score'] = 0;
    $_SESSION['bot_score'] = 0;
    $_SESSION['rounds_played'] = 0;
    $_SESSION['target'] = null; // first-to-X wins
}

// handle reset/new game
if (isset($_POST['reset'])) {
    $_SESSION['player_score'] = 0;
    $_SESSION['bot_score'] = 0;
    $_SESSION['rounds_played'] = 0;
    $_SESSION['target'] = null;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// set target (start game)
if (isset($_POST['set_target'])) {
    $t = intval($_POST['target'] ?? 0);
    if ($t > 0) {
        $_SESSION['target'] = $t;
        $_SESSION['player_score'] = 0;
        $_SESSION['bot_score'] = 0;
        $_SESSION['rounds_played'] = 0;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$result = null;
$resultClass = "";
$player = null;
$bot = null;
$gameOver = false;
$finalMessage = "";

// play a round only when target is set and game not over
if (isset($_POST['choice']) && $_SESSION['target'] !== null) {
    $player = $_POST['choice'];
    $bot = $options[array_rand($options)];
    $_SESSION['rounds_played']++;

    if ($player == $bot) {
        $result = "Draw!";
        $resultClass = "draw";
    } elseif (
        ($player=="rock" && $bot=="scissors") ||
        ($player=="scissors" && $bot=="paper") ||
        ($player=="paper" && $bot=="rock")
    ) {
        $result = "You win this round!";
        $resultClass = "win";
        $_SESSION['player_score']++;
    } else {
        $result = "Bot wins this round!";
        $resultClass = "lose";
        $_SESSION['bot_score']++;
    }

    // check for final winner (first to target)
    if ($_SESSION['player_score'] >= $_SESSION['target'] || $_SESSION['bot_score'] >= $_SESSION['target']) {
        $gameOver = true;
        if ($_SESSION['player_score'] > $_SESSION['bot_score']) {
            $finalMessage = "You won the match!";
        } elseif ($_SESSION['player_score'] < $_SESSION['bot_score']) {
            $finalMessage = "Bot won the match!";
        } else {
            $finalMessage = "Match ended in a tie.";
        }
    }
} elseif ($_SESSION['target'] !== null) {
    // no play yet, but game started
    if ($_SESSION['player_score'] >= $_SESSION['target'] || $_SESSION['bot_score'] >= $_SESSION['target']) {
        $gameOver = true;
        if ($_SESSION['player_score'] > $_SESSION['bot_score']) {
            $finalMessage = "You won the match!";
        } elseif ($_SESSION['player_score'] < $_SESSION['bot_score']) {
            $finalMessage = "Bot won the match!";
        } else {
            $finalMessage = "Match ended in a tie.";
        }
    }
}

// helper for disabling choices when game over or no target set
$disableChoices = ($_SESSION['target'] === null) || $gameOver;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Rock Paper Scissors - Match Play</title>
    <style>
        :root{
            --bg:#0f1724;
            --card:#0b1220;
            --muted:#9aa7bb;
            --win:#16a34a;
            --lose:#ef4444;
            --draw:#94a3b8;
            --accent:#2563eb;
        }
        *{box-sizing:border-box}
        body{
            background:linear-gradient(135deg,#071036 0%, #00121a 100%);
            color:#e6eef8;
            font-family:Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            margin:0;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:32px;
        }
        .wrap{
            width:100%;
            max-width:760px;
            background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border:1px solid rgba(255,255,255,0.04);
            padding:28px;
            border-radius:12px;
            box-shadow: 0 8px 30px rgba(2,6,23,0.6);
        }
        h1{margin:0 0 12px 0;font-size:20px;letter-spacing:0.2px}
        p.lead{margin:0 0 18px 0;color:var(--muted)}
        .choices{
            display:flex;
            gap:12px;
            justify-content:center;
            margin-bottom:20px;
            flex-wrap:wrap;
        }
        button.choice{
            background:transparent;
            border:2px solid rgba(255,255,255,0.06);
            color:var(--accent);
            font-size:18px;
            padding:14px 20px;
            border-radius:10px;
            cursor:pointer;
            transition:transform .12s ease, box-shadow .12s ease, background .12s ease;
            display:flex;
            gap:12px;
            align-items:center;
            min-width:140px;
            justify-content:center;
            backdrop-filter: blur(6px);
        }
        button.choice:hover{ transform:translateY(-4px) scale(1.02); box-shadow:0 8px 24px rgba(37,99,235,0.12) }
        button.choice .emoji{ font-size:34px; filter: drop-shadow(0 6px 14px rgba(0,0,0,0.6)); }
        button.choice.selected{
            border-color: rgba(255,255,255,0.12);
            background:linear-gradient(90deg, rgba(37,99,235,0.12), rgba(37,99,235,0.06));
            color:#dbeafe;
            box-shadow:0 10px 30px rgba(37,99,235,0.14);
            transform:translateY(-6px);
        }
        button.choice:disabled{ opacity:0.45; cursor:not-allowed; transform:none; box-shadow:none }
        .result {
            display:flex;
            gap:16px;
            align-items:center;
            justify-content:space-between;
            padding:14px;
            border-radius:10px;
            margin-top:8px;
            border:1px solid rgba(255,255,255,0.03);
            background:linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.00));
        }
        .pill{ font-size:14px; color:var(--muted) }
        .card{
            flex:1;
            display:flex;
            gap:12px;
            align-items:center;
            padding:10px;
            border-radius:8px;
            background:linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.00));
            border:1px solid rgba(255,255,255,0.02);
        }
        .icon{
            width:78px;height:78px;border-radius:10px;
            display:flex;align-items:center;justify-content:center;
            font-size:40px;
            background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            box-shadow: 0 8px 18px rgba(2,6,23,0.6);
        }
        .status{font-weight:600;}
        .status.win{ color:var(--win) }
        .status.lose{ color:var(--lose) }
        .status.draw{ color:var(--draw) }
        .muted{ color:var(--muted); font-size:13px }
        .center { text-align:center; width:100% }
        .footer { margin-top:14px; color:var(--muted); font-size:13px; text-align:center }
        .top-controls{ display:flex; gap:12px; align-items:center; margin-bottom:12px; justify-content:space-between }
        .scoreboard{ display:flex; gap:12px; align-items:center }
        .score-pill{ background:rgba(255,255,255,0.02); padding:8px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.02) }
        @media (max-width:520px){
            .choices{gap:8px}
            button.choice{min-width:110px;padding:12px}
            .icon{width:64px;height:64px;font-size:32px}
            .top-controls{flex-direction:column; align-items:stretch}
        }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Rock • Paper • Scissors — Match Play</h1>
    <p class="lead">Choose how many wins are needed (first-to). Play rounds until someone reaches the target. Reset to start a new match.</p>

    <div class="top-controls">
        <form method="post" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <?php if ($_SESSION['target'] === null): ?>
                <label class="muted" style="margin-right:8px;font-weight:600">First to</label>
                <!-- submitting any number starts the match; include hidden flag so server recognizes set_target -->
                <input type="hidden" name="set_target" value="1" />
                <div class="choices" aria-label="Choose target wins" style="justify-content:flex-start;gap:8px">
                    <?php for ($i = 1; $i <= 11; $i++): ?>
                        <button
                            class="choice"
                            type="submit"
                            name="target"
                            value="<?php echo $i ?>"
                            title="First to <?php echo $i ?>"
                            style="min-width:52px;padding:10px 12px;font-weight:700;color:#dbeafe;border-color:rgba(255,255,255,0.06);
                                   background: linear-gradient(90deg, rgba(37,99,235,0.14), rgba(37,99,235,0.06));
                                   box-shadow:0 8px 18px rgba(37,99,235,0.06)"
                            aria-label="First to <?php echo $i ?>"
                        >
                            <?php echo $i ?>
                        </button>
                    <?php endfor; ?>
                </div>
                <div class="muted" style="font-size:13px;margin-left:6px">Click a number to start</div>
            <?php else: ?>
                <div class="scoreboard">
                    <div class="score-pill"><strong>You:</strong> <?php echo $_SESSION['player_score'] ?></div>
                    <div class="score-pill"><strong>Bot:</strong> <?php echo $_SESSION['bot_score'] ?></div>
                    <div class="score-pill muted">Target: <?php echo $_SESSION['target'] ?></div>
                    <div class="score-pill muted">Rounds: <?php echo $_SESSION['rounds_played'] ?></div>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <form method="post" style="display:block">
        <div class="choices" aria-hidden="<?php echo $disableChoices ? 'true' : 'false' ?>">
            <?php foreach ($options as $opt):
                $sel = ($player === $opt) ? "selected" : "";
                $disabled = $disableChoices ? "disabled" : "";
            ?>
            <button class="choice <?php echo $sel ?>" name="choice" value="<?php echo htmlspecialchars($opt) ?>" type="submit" title="<?php echo ucfirst($opt) ?>" <?php echo $disabled ?>>
                <span class="emoji"><?php echo $emojis[$opt] ?></span>
                <span class="label"><?php echo ucfirst($opt) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
    </form>
                <center><form method="post" style="margin-left:auto;">
            <button class="choice" name="reset" type="submit">New game</button>
        </form></center>
    <?php if ($result !== null): ?>
    <div class="result" role="status" aria-live="polite">
        <div class="card">
            <div class="icon"><?php echo $player ? $emojis[$player] : "" ?></div>
            <div>
                <div class="pill">You</div>
                <div class="status <?php echo $resultClass ?>"><?php echo htmlspecialchars($player) ?></div>
            </div>
        </div>

        <div style="text-align:center;min-width:160px;">
            <div class="muted">Round Result</div>
            <div class="status <?php echo $resultClass ?>" style="font-size:18px;margin-top:6px"><?php echo $result ?></div>
            <div class="muted" style="margin-top:6px">VS</div>
        </div>

        <div class="card">
            <div>
                <div class="pill">Bot</div>
                <div class="status <?php echo $resultClass ?>"><?php echo htmlspecialchars($bot) ?></div>
            </div>
            <div class="icon"><?php echo $bot ? $emojis[$bot] : "" ?></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($gameOver): ?>
        <div class="result" style="margin-top:12px;flex-direction:column;align-items:center;">
            <div class="status" style="font-size:18px;margin-bottom:6px"><?php echo htmlspecialchars($finalMessage) ?></div>
            <div class="muted">Final score — You <?php echo $_SESSION['player_score'] ?> : <?php echo $_SESSION['bot_score'] ?> Bot</div>
            <form method="post" style="margin-top:10px;">
                <button class="choice" name="reset" type="submit">Start New Match</button>
            </form>
        </div>
    <?php elseif ($_SESSION['target'] === null): ?>
        <div class="footer">Pick a target to start the match.</div>
    <?php else: ?>
        <div class="footer">Target: first to <?php echo $_SESSION['target'] ?> wins. Good luck.</div>
    <?php endif; ?>
</div>
</body>
</html>