<?php
require_once 'db_connect.php';
$pdo = getDbConnection();

// Today's date
$today = date("Y-m-d");

// Get attendance counts and participation
$stmt = $pdo->prepare("
    SELECT 
        SUM(status = 'absent') AS total_absent,
        SUM(status = 'present') AS total_present,
        COUNT(*) AS total_records,
        SUM(participation) AS total_participation
    FROM attendance 
    WHERE date = ?
");
$stmt->execute([$today]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$totalAbsent = $data["total_absent"] ?? 0;
$totalPresent = $data["total_present"] ?? 0;
$totalStudents = $totalAbsent + $totalPresent;
$totalParticipation = $data["total_participation"] ?? 0;

// Average participation as a percentage of present students
$avgParticipation = $totalPresent > 0 ? round(($totalParticipation / $totalPresent) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<h1>Attendance Report for <?= $today ?></h1>

<?php if($totalStudents == 0): ?>
    <p style="color:red;">No attendance data found for today.</p>
<?php else: ?>
    <p><b>Total Students:</b> <?= $totalStudents ?></p>
    <p><b>Total Present:</b> <?= $totalPresent ?></p>
    <p><b>Total Absent:</b> <?= $totalAbsent ?></p>
    <p><b>Total Participation:</b> <?= $totalParticipation ?> / <?= $totalPresent ?> present students</p>
    <p><b>Average Participation:</b> <?= $avgParticipation ?>%</p>

    <div style="width:350px; margin:auto;">
        <canvas id="attendanceChart" width="250" height="250"></canvas>
    </div>
    <br>
    <div style="width:350px; margin:auto;">
        <canvas id="participationChart" width="250" height="250"></canvas>
    </div>
    

    <br>
    <a href="index.php" style="
    display: inline-block;
    text-decoration: none;
    background-color: #36a2eb;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.3s, transform 0.2s;
    margin-top: 10px;
" 
onmouseover="this.style.backgroundColor='#1a73e8'; this.style.transform='scale(1.05)';" 
onmouseout="this.style.backgroundColor='#36a2eb'; this.style.transform='scale(1)';"
>
⬅ Back to Home
</a>
    <script>
        // Attendance donut
        const ctxAttendance = document.getElementById("attendanceChart");
        new Chart(ctxAttendance, {
            type: "doughnut",
            data: {
                labels: ["Absent", "Present"],
                datasets: [{
                    data: [<?= $totalAbsent ?>, <?= $totalPresent ?>],
                    backgroundColor: ["#ff6384", "#36a2eb"]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: "bottom" },
                    title: { display: true, text: "Attendance Summary" }
                }
            }
        });

        // Participation donut
        const ctxParticipation = document.getElementById("participationChart");
        new Chart(ctxParticipation, {
            type: "doughnut",
            data: {
                labels: ["Participated", "Did Not Participate"],
                datasets: [{
                    data: [<?= $totalParticipation ?>, <?= $totalPresent - $totalParticipation ?>],
                    backgroundColor: ["#4caf50", "#ff9800"]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: "bottom" },
                    title: { display: true, text: "Participation Summary for present student" }
                }
            }
        });
    </script>
<?php endif; ?>

</body>
</html>
