<?php
require_once 'db_connect.php';
$pdo = getDbConnection();

// === Load all students ===
$stmt = $pdo->query("SELECT * FROM students ORDER BY student_id ASC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// === Load today's attendance ===
$today = date("Y-m-d");
$attendanceStmt = $pdo->prepare("SELECT student_id, status, participation FROM attendance WHERE date = ?");
$attendanceStmt->execute([$today]);
$attendanceData = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);

// === Map student_id → [status, participation] ===
$attendanceMap = [];
foreach ($attendanceData as $a) {
    $attendanceMap[$a['student_id']] = [
        'status' => $a['status'],
        'participation' => $a['participation']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance Dashboard</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* --- Navbar CSS --- */
.nav {
    background-color: #333;
    padding: 10px;
    margin-bottom: 20px;

    display: flex;
    justify-content: space-between; /* Spread left → right */
    align-items: center;
}

.nav a {
    color: #f2f2f2;
    text-decoration: none;
    font-weight: bold;
    padding: 8px 16px;
}

.nav a:hover {
    background-color: #ddd;
    color: black;
}


/* --- Table & Highlight --- */
#attendanceTable { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
#attendanceTable th, #attendanceTable td { border: 1px solid #ccc; padding: 8px; text-align: center; }
#attendanceTable tr:hover { background-color: #f1f1f1; cursor: pointer; }
.good { background: #c8f7c5; }
.bad { background: #f7c5c5; }
.highlight-excellent { background-color: #27c300ff !important; }

/* --- Buttons --- */
.highlight-section { margin-bottom: 20px; }
.highlight-section button { padding: 8px 12px; margin-right: 10px; cursor: pointer; }
.report-section { margin-top: 20px; text-align: center; }
</style>
</head>
<body>

<h1>Attendance Dashboard - <?= $today ?></h1>

<!-- NAVIGATION -->
<nav class="nav">
    <a href="index.php" class="left">Home</a>
   
    <a href="take_attendance.php">Take Attendance</a>
    
    <a href="#add">Add Student</a>
   
    <a href="reports.php">Reports</a>
   
    <a href="#">Logout</a>
</nav>

<!-- STUDENT TABLE -->
<table id="attendanceTable">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Matricule</th>
        <th>Group</th>
        <th>Absences</th>
        <th>Participation</th>
        <th>Message</th>
    </tr>
    <?php foreach($students as $s): 
        $status = $attendanceMap[$s['student_id']]['status'] ?? 'absent';
        $participation = $attendanceMap[$s['student_id']]['participation'] ?? 0;

        // If absent, participation must be 0
        if($status === 'absent') $participation = 0;

        $abs = ($status === 'absent') ? 1 : 0;
        $par = $participation ? 1 : 0;

        // Message logic
        if($abs) $msg = "Absent today";
        else $msg = $par ? "Present & Participated" : "Present, No participation";

        $rowClass = $abs ? "bad" : ($par ? "good" : "");
    ?>
    <tr class="<?= $rowClass ?>">
        <td><?= htmlspecialchars($s["student_id"]) ?></td>
        <td><?= htmlspecialchars($s["fullname"]) ?></td>
        <td><?= htmlspecialchars($s["matricule"]) ?></td>
        <td><?= htmlspecialchars($s["group_id"]) ?></td>
        <td class="abs"><?= $abs ?> Abs</td>
        <td class="par"><?= $par ?> Par</td>
        <td><?= htmlspecialchars($msg) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- EXERCISE 6 BUTTONS -->
<div class="highlight-section">
    <button id="highlightExcellent">🌟 Highlight Excellent Students</button>
    <button id="resetColors">🔄 Reset Colors</button>
</div>

<!-- ADD STUDENT -->
<h2 id="add">Add Student</h2>
<form id="studentForm" action="add_student.php" method="post">
    <label>Full Name:</label>
    <input type="text" name="fullname" required>
    <label>Matricule:</label>
    <input type="text" name="matricule" required>
    <label>Group:</label>
    <input type="text" name="group_id" required>
    <button type="submit">Add Student</button>
</form>
<div id="formMessage"></div>

<!-- SESSION MANAGEMENT -->
<h2>Create Attendance Session</h2>
<form action="create_session.php" method="post">
    Course ID: <input type="text" name="course_id"><br>
    Group ID: <input type="text" name="group_id"><br>
    Professor ID: <input type="text" name="prof_id"><br>
    <button type="submit">Create Session</button>
</form>

<h2>Close Session</h2>
<form action="close_session.php" method="post">
    Session ID: <input type="text" name="session_id"><br>
    <button type="submit">Close Session</button>
</form>

<!-- REPORT SECTION -->
<div class="report-section">
    <button id="generateReport">Generate Report</button>
    <div id="reportOutput"></div>
    <canvas id="reportChart" width="250" height="250"></canvas>
</div>

<script>
// === Highlight & Reset ===
document.getElementById("highlightExcellent").onclick = function() {
    document.querySelectorAll("#attendanceTable tr").forEach((row, i) => {
        if(i === 0) return;
        const par = parseInt(row.querySelector(".par").textContent) || 0;
        const abs = parseInt(row.querySelector(".abs").textContent) || 0;
        if(abs === 0 && par === 1) row.classList.add("highlight-excellent");
    });
};
document.getElementById("resetColors").onclick = function() {
    document.querySelectorAll("#attendanceTable tr").forEach(row => {
        row.classList.remove("highlight-excellent");
    });
};

// === Generate Daily Report ===
document.getElementById("generateReport").onclick = function() {
    const rows = document.querySelectorAll("#attendanceTable tr:not(:first-child)");
    let totalAbs = 0, totalPar = 0, count = 0;
    rows.forEach(row => {
        count++;
        totalAbs += parseInt(row.querySelector(".abs").textContent) || 0;
        totalPar += parseInt(row.querySelector(".par").textContent) || 0;
    });
    const avgPar = (count>0 ? (totalPar/count).toFixed(2) : 0);
    document.querySelector("#reportOutput").innerHTML =
        `<b>Total Students:</b> ${count}<br>
         <b>Total Absences:</b> ${totalAbs}<br>
         <b>Average Participation:</b> ${avgPar}/1`;

    const totalPresent = count - totalAbs;
    const ctx = document.getElementById("reportChart");
    new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: ["Absences", "Presences"],
            datasets: [{
                data: [totalAbs, totalPresent],
                backgroundColor: ["#ff4d4d","#4da6ff"]
            }]
        },
        options: { responsive: false, cutout: "60%" }
    });
};
</script>

</body>
</html>
