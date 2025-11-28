// === UTILITY: Update Attendance & Row Colors ===
function updateAttendance() {
    $("#attendanceTable tr").each(function(index) {
        if(index === 0) return; // skip header

        const boxes = $(this).find("input[type='checkbox']");
        const attendance = boxes.slice(0, 6);
        const participation = boxes.slice(6, 12);

        const abs = 6 - attendance.filter((i, cb) => $(cb).prop("checked")).length;
        const par = participation.filter((i, cb) => $(cb).prop("checked")).length;

        $(this).find(".abs").text(abs + " Abs");
        $(this).find(".par").text(par + " Par");

        $(this).removeClass("good warning bad");
        if(abs < 3) $(this).addClass("good");
        else if(abs <= 4) $(this).addClass("warning");
        else $(this).addClass("bad");

        const msg = $(this).find(".msg");
        if(abs < 3 && par >= 4) msg.text("Good attendance – Excellent participation");
        else if(abs >= 3 && abs <= 4) msg.text("Warning – attendance low – You need to participate more");
        else msg.text("Excluded – too many absences – You need to participate more");
    });
}

// === LOAD STUDENTS + ATTENDANCE FROM DATABASE ===
function loadStudents() {
    $.getJSON("get_students.php", function(data) {
        $("#attendanceTable tr:gt(0)").remove(); // remove old rows
        data.forEach(s => {
            const row = $("<tr>");
            row.append($("<td>").text(s.student_id));
            row.append($("<td>").text(s.name));
            row.append($("<td>").text(s.group_name));

            for(let i=0;i<6;i++) row.append($("<td>").html('<input type="checkbox" '+(s.attendance && s.attendance[i] ? 'checked' : '')+'>'));
            for(let i=0;i<6;i++) row.append($("<td>").html('<input type="checkbox" '+(s.participation && s.participation[i] ? 'checked' : '')+'>'));

            row.append($("<td>").addClass("abs"));
            row.append($("<td>").addClass("par"));
            row.append($("<td>").addClass("msg"));

            $("#attendanceTable").append(row);
        });

        // Attach checkbox listener
        $("#attendanceTable input[type='checkbox']").off("change").on("change", function(){
            updateAttendance();
            saveAttendance();
        });

        updateAttendance();
    });
}

// === SAVE ATTENDANCE TO DATABASE ===
function saveAttendance() {
    const attendanceData = [];
    $("#attendanceTable tr").slice(1).each(function(){
        const student_id = $(this).find("td:first").text();
        const attendance = [];
        const participation = [];
        $(this).find("td").slice(3,9).each(function(){ attendance.push($(this).find("input").prop("checked")); });
        $(this).find("td").slice(9,15).each(function(){ participation.push($(this).find("input").prop("checked")); });

        attendanceData.push({ student_id, attendance, participation });
    });

    $.ajax({
        url: "save_attendance.php",
        method: "POST",
        data: JSON.stringify({ attendance: attendanceData }),
        contentType: "application/json",
        success: function(resp){ console.log("Attendance saved", resp); }
    });
}

// === DOCUMENT READY ===
$(document).ready(function() {
    loadStudents();

    // Add student form
    $("#studentForm").submit(function(e) {
        e.preventDefault();

        const studentId = $("#student_id").val().trim();
        const name = $("#name").val().trim();
        const group = $("#group").val().trim();

        $(".error").remove();
        $("#formMessage").text("");

        let valid = true;
        if(!/^[0-9]+$/.test(studentId)){ $("#student_id").after('<small class="error">Student ID must contain only digits.</small>'); valid=false; }
        if(!/^[A-Za-z ]+$/.test(name)){ $("#name").after('<small class="error">Name must contain only letters and spaces.</small>'); valid=false; }
        if(!/^[A-Za-z0-9-_]+$/.test(group)){ $("#group").after('<small class="error">Group must contain letters/numbers only.</small>'); valid=false; }
        if(!valid) return;

        // Save via PHP
        $.post("add_student.php", { student_id: studentId, name, group_name: group }, function(){
            $("#formMessage").html('<span style="color:green;">✅ Student added successfully!</span>');
            $("#studentForm")[0].reset();
            loadStudents();
        });
    });

    // Report Chart
$("#generateReport").click(function(){
    $.getJSON("get_report.php", function(data){
        $("#reportOutput").html(`🧾 <b>Class Report</b><br>
            Total Students: ${data.totalStudents}<br>
            Total Absences: ${data.totalAbsences}<br>
            Average Participation: ${data.averageParticipation}/6`
        );

        const totalPossible = data.totalStudents * 6;
        const totalPresent = totalPossible - data.totalAbsences;

        if(reportChart) reportChart.destroy();
        const ctx = $("#reportChart");
        reportChart = new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: ["Absences","Presences"],
                datasets:[{ data:[data.totalAbsences,totalPresent], backgroundColor:["#ff6384","#36a2eb"] }]
            },
            options:{
                responsive:true,
                cutout:"60%",
                plugins:{
                    legend:{ position:"bottom" },
                    title:{ display:true, text:"Attendance Summary" }
                }
            }
        });
    });
});

    // Hover + Click
    $("#attendanceTable tr").hover(
        function(){ if($(this).index()!==0) $(this).addClass("highlight"); },
        function(){ $(this).removeClass("highlight"); }
    );
    $("#attendanceTable tr").click(function(){
        if($(this).index()===0) return;
        const name = $(this).find("td:nth-child(2)").text();
        const studentId = $(this).find("td:nth-child(1)").text();
        const absText = $(this).find(".abs").text();
        alert(`👩‍🎓 Student: ${name} (ID: ${studentId})\n📘 ${absText || "No absences recorded"}`);
    });

    // Highlight Excellent / Reset
    $("#highlightExcellent").click(function(){
        updateAttendance();
        $("#attendanceTable tr").slice(1).each(function(){
            const absNum = parseInt($(this).find(".abs").text()) || 0;
            if(absNum < 3) $(this).fadeOut(150).fadeIn(150).addClass("highlight-excellent");
        });
    });
    $("#resetColors").click(function(){
        $("#attendanceTable tr").removeClass("highlight-excellent");
        updateAttendance();
    });

    // Search
    $("#searchInput").on("keyup", function(){
        const val = $(this).val().toLowerCase();
        $("#attendanceTable tr").slice(1).each(function(){
            const name = $(this).find("td:nth-child(2)").text().toLowerCase();
            $(this).toggle(name.includes(val));
        });
    });

    // Sorting
    function sortRows(compareFunc){
        const rows = $("#attendanceTable tr").slice(1).get();
        rows.sort(compareFunc);
        rows.forEach(row=> $("#attendanceTable").append(row));
        updateAttendance();
    }
    $("#sortAbs").click(function(){ sortRows((a,b)=> parseInt($(a).find(".abs").text()) - parseInt($(b).find(".abs").text())); $("#sortStatus").text("Currently sorted by absences (ascending)"); });
    $("#sortPar").click(function(){ sortRows((a,b)=> parseInt($(b).find(".par").text()) - parseInt($(a).find(".par").text())); $("#sortStatus").text("Currently sorted by participation (descending)"); });
});
