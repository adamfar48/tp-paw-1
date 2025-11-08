// === EXERCISE 1 : Update Attendance & Row Colors ===
function updateAttendance() {
  const rows = document.querySelectorAll("#attendanceTable tr");
  rows.forEach((row, i) => {
    if (i === 0) return;

    const boxes = row.querySelectorAll("input[type='checkbox']");
    const attendance = Array.from(boxes).slice(0, 6);
    const participation = Array.from(boxes).slice(6, 12);

    const abs = attendance.length - attendance.filter(cb => cb.checked).length;
    const par = participation.filter(cb => cb.checked).length;

    row.querySelector(".abs").textContent = abs + " Abs";
    row.querySelector(".par").textContent = par + " Par";

    row.classList.remove("good", "warning", "bad");
    if (abs < 3) row.classList.add("good");
    else if (abs >= 3 && abs <= 4) row.classList.add("warning");
    else row.classList.add("bad");

    const msg = row.querySelector(".msg");
    if (abs < 3 && par >= 4)
      msg.textContent = "Good attendance – Excellent participation";
    else if (abs >= 3 && abs <= 4)
      msg.textContent = "Warning – attendance low – You need to participate more";
    else
      msg.textContent = "Excluded – too many absences – You need to participate more";
  });
}

document.addEventListener("DOMContentLoaded", () => {
  updateAttendance();
  document.querySelectorAll("input[type='checkbox']").forEach(cb =>
    cb.addEventListener("change", updateAttendance)
  );
});

// === EXERCISE 2–3 : Validation + Add Student ===
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("studentForm");
  const table = document.getElementById("attendanceTable");

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const studentId = document.getElementById("studentId");
    const lastName = document.getElementById("lastName");
    const firstName = document.getElementById("firstName");
    const email = document.getElementById("email");

    const idError = document.getElementById("idError");
    const lastError = document.getElementById("lastNameError");
    const firstError = document.getElementById("firstNameError");
    const emailError = document.getElementById("emailError");

    [idError, lastError, firstError, emailError].forEach(e => e.textContent = "");
    [studentId, lastName, firstName, email].forEach(i => i.classList.remove("error-border"));

    let valid = true;

    if (studentId.value.trim() === "" || !/^[0-9]+$/.test(studentId.value.trim())) {
      idError.textContent = "Student ID must contain only numbers.";
      studentId.classList.add("error-border");
      valid = false;
    }
    if (lastName.value.trim() === "" || !/^[A-Za-z]+$/.test(lastName.value.trim())) {
      lastError.textContent = "Last Name must contain only letters.";
      lastName.classList.add("error-border");
      valid = false;
    }
    if (firstName.value.trim() === "" || !/^[A-Za-z]+$/.test(firstName.value.trim())) {
      firstError.textContent = "First Name must contain only letters.";
      firstName.classList.add("error-border");
      valid = false;
    }
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (email.value.trim() === "" || !emailPattern.test(email.value.trim())) {
      emailError.textContent = "Please enter a valid email address.";
      email.classList.add("error-border");
      valid = false;
    }

    if (valid) {
      const newRow = document.createElement("tr");
      newRow.innerHTML = `
        <td>${lastName.value}</td>
        <td>${firstName.value}</td>
        ${Array(6).fill('<td><input type="checkbox"></td>').join("")}
        ${Array(6).fill('<td><input type="checkbox"></td>').join("")}
        <td class="abs"></td>
        <td class="par"></td>
        <td class="msg"></td>
      `;
      table.appendChild(newRow);
      newRow.querySelectorAll("input[type='checkbox']").forEach(cb =>
        cb.addEventListener("change", updateAttendance)
      );
      updateAttendance();
      alert("✅ Student added successfully!");
      form.reset();
    }
  });
});

// === EXERCISE 4 : Generate Attendance Report + Donut Chart ===
document.addEventListener("DOMContentLoaded", () => {
  const reportBtn = document.getElementById("generateReport");
  const reportOutput = document.getElementById("reportOutput");
  const ctx = document.getElementById("reportChart");
  let reportChart = null;

  reportBtn.addEventListener("click", () => {
    const rows = document.querySelectorAll("#attendanceTable tr");
    let studentCount = 0, totalAbsences = 0, totalParticipation = 0, participationCount = 0;

    rows.forEach((row, i) => {
      if (i === 0) return;
      studentCount++;
      const absCell = row.querySelector(".abs");
      const parCell = row.querySelector(".par");
      if (absCell && parCell) {
        const absValue = parseInt(absCell.textContent) || 0;
        const parValue = parseInt(parCell.textContent) || 0;
        totalAbsences += absValue;
        totalParticipation += parValue;
        participationCount++;
      }
    });

    const avgParticipation = participationCount > 0
      ? (totalParticipation / participationCount).toFixed(2)
      : 0;
    reportOutput.innerHTML = `
      🧾 <b>Class Report</b><br>
      Total Students: ${studentCount}<br>
      Total Absences: ${totalAbsences}<br>
      Average Participation: ${avgParticipation} / 6
    `;

    const totalPossible = studentCount * 6;
    const totalPresent = totalPossible - totalAbsences;
    if (reportChart) reportChart.destroy();

    reportChart = new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Absences", "Presences"],
        datasets: [{
          data: [totalAbsences, totalPresent],
          backgroundColor: ["#ff6384", "#36a2eb"]
        }]
      },
      options: {
        responsive: true,
        cutout: "60%",
        plugins: {
          legend: { position: "bottom" },
          title: { display: true, text: "Attendance Summary" }
        }
      }
    });
  });
});

// === EXERCISE 5 : jQuery Hover & Click ===
$(document).ready(function() {
  $("#attendanceTable tr").hover(
    function() { if ($(this).index() !== 0) $(this).addClass("highlight"); },
    function() { $(this).removeClass("highlight"); }
  );

  $("#attendanceTable tr").click(function() {
    if ($(this).index() === 0) return;
    const lastName = $(this).find("td:nth-child(1)").text().trim();
    const firstName = $(this).find("td:nth-child(2)").text().trim();
    const absText = $(this).find(".abs").text().trim();
    alert(`👩‍🎓 Student: ${firstName} ${lastName}\n📘 ${absText || "No absences recorded"}`);
  });
});
