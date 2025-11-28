<?php
include "../PHP/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $thesis_id = $_POST['thesis_id'];
    $student_name = $_POST['student_name'];
    $student_no = $_POST['student_no'];
    $course_section = $_POST['course_section'];

    // Generate unique 3-digit request number (e.g. #123)
    do {
        $random_num = rand(100, 999);
        $formatted_number = "#" . $random_num;

        $check = $conn->query("SELECT * FROM tbl_borrow_requests WHERE request_number = '$formatted_number'");
    } while ($check->num_rows > 0);

    // Insert the request
    $sql = "INSERT INTO tbl_borrow_requests (thesis_id, request_number, student_name, student_no, course_section)
            VALUES ('$thesis_id', '$formatted_number', '$student_name', '$student_no', '$course_section')";

    if ($conn->query($sql) === TRUE) {
        // Fetch related thesis info
        $thesis = $conn->query("SELECT * FROM tbl_thesis WHERE thesis_id = $thesis_id")->fetch_assoc();

        // Show confirmation preview
?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Request Confirmation</title>
            <link rel="icon" type="image/png" href="user-pictures/logo.png">
            <link rel="stylesheet" href="user-style.css">

        </head>

        <body>
            <div class="confirmation-box">
                <a href="catalog.php" class="back-btn-icon">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </a>
                <div class="success-icon">
                    <svg viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="45" stroke="#2e7d32" stroke-width="5" fill="none" />
                        <path d="M30 52 L45 67 L70 40" stroke="#2e7d32" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <h2>Request Submitted Successfully!</h2>
                <p class="request-number">Request Number: <?php echo $formatted_number; ?></p>

                <div class="preview-info">
                    <h4>Student Information</h4>
                    <div class="label">Student Name</div>
                    <div class="value"><?php echo htmlspecialchars($student_name); ?></div>

                    <div class="label">Student No.</div>
                    <div class="value"><?php echo htmlspecialchars($student_no); ?></div>

                    <div class="label">Course & Section</div>
                    <div class="value"><?php echo htmlspecialchars($course_section); ?></div>

                    <br><br>

                    <h4>Thesis Information</h4>
                    <div class="label">Thesis Title</div>
                    <div class="value"><?php echo htmlspecialchars($thesis['title']); ?></div>

                    <div class="label">Author(s)</div>
                    <div class="value"><?php echo htmlspecialchars($thesis['author']); ?></div>

                    <div class="label">Department</div>
                    <div class="value"><?php echo htmlspecialchars($thesis['department']); ?></div>

                    <div class="label">Year</div>
                    <div class="value"><?php echo htmlspecialchars($thesis['year']); ?></div>
                </div>


                <form action="catalog.php" method="get">

                    <!-- <button type="button" class="download-btn pdf" onclick="downloadPDF()">Download PDF</button> -->

                </form>
            </div>

            <!-- Required Libraries -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

            <script>
                // === 🔧 Helper: Inline all computed CSS so colors render perfectly ===
                function applyComputedStyles(el) {
                    const computed = window.getComputedStyle(el);
                    for (let key of computed) {
                        el.style[key] = computed.getPropertyValue(key);
                    }
                    for (let child of el.children) applyComputedStyles(child);
                }

                // === 🧾 Download as PDF with Header & Footer ===
                async function downloadPDF() {
                    const {
                        jsPDF
                    } = window.jspdf;

                    const pdf = new jsPDF("landscape", "mm", "a4");
                    const pageWidth = pdf.internal.pageSize.getWidth();
                    const pageHeight = pdf.internal.pageSize.getHeight();

                    const marginX = 20;
                    const marginTop = 20;
                    let y = marginTop;

                    /* Utility: Auto-break function */
                    function checkPageBreak(addHeight = 10) {
                        if (y + addHeight > pageHeight - 20) {
                            pdf.addPage();
                            y = marginTop;
                        }
                    }

                    /* -----------------------------------------
                       HEADER
                    ----------------------------------------- */
                    const logo = new Image();
                    logo.src = "user-pictures/logo.png";

                    logo.onload = function() {
                        pdf.addImage(logo, "PNG", marginX, y, 20, 20);

                        pdf.setFont("helvetica", "bold");
                        pdf.setFontSize(16);
                        pdf.text("CEIT Thesis Hub", marginX + 28, y + 7);

                        pdf.setFontSize(11);
                        pdf.text("Pamantasan ng Lungsod ng Valenzuela", marginX + 28, y + 14);

                        y += 30;

                        /* -----------------------------------------
                           TITLE
                        ----------------------------------------- */
                        pdf.setFont("helvetica", "bold");
                        pdf.setFontSize(15);
                        pdf.text("Borrow Request Receipt", marginX, y);
                        y += 10;

                        pdf.setFont("helvetica", "normal");
                        pdf.setFontSize(11);
                        pdf.text("Request No.: <?php echo $formatted_number; ?>", marginX, y);
                        y += 15;

                        /* DIVIDER */
                        pdf.setDrawColor(180);
                        pdf.line(marginX, y, pageWidth - marginX, y);
                        y += 10;

                        /* -----------------------------------------
                           STUDENT INFORMATION
                        ----------------------------------------- */
                        pdf.setFont("helvetica", "bold");
                        pdf.setFontSize(13);
                        pdf.text("Student Information", marginX, y);
                        y += 7;

                        pdf.setDrawColor(210);
                        pdf.line(marginX, y, pageWidth - marginX, y);
                        y += 7;

                        /* NAME */
                        let name = "<?php echo htmlspecialchars($student_name); ?>";
                        let wrapName = pdf.splitTextToSize(name, 230);

                        pdf.setFont("helvetica", "bold");
                        pdf.setFontSize(11);
                        pdf.text("Name:", marginX, y);

                        pdf.setFont("helvetica", "normal");
                        pdf.text(wrapName, marginX + 25, y);

                        y += wrapName.length * 6 + 4;
                        checkPageBreak();

                        /* STUDENT NUMBER */
                        pdf.setFont("helvetica", "bold");
                        pdf.text("Student No.:", marginX, y);

                        pdf.setFont("helvetica", "normal");
                        pdf.text("<?php echo htmlspecialchars($student_no); ?>", marginX + 30, y);

                        y += 8;
                        checkPageBreak();

                        /* COURSE */
                        pdf.setFont("helvetica", "bold");
                        pdf.text("Course & Section:", marginX, y);

                        pdf.setFont("helvetica", "normal");
                        pdf.text("<?php echo htmlspecialchars($course_section); ?>", marginX + 40, y);

                        y += 15;
                        checkPageBreak();


                        /* -----------------------------------------
                           THESIS INFORMATION
                        ----------------------------------------- */
                        pdf.setDrawColor(210);
                        pdf.line(marginX, y, pageWidth - marginX, y);
                        y += 10;

                        pdf.setFont("helvetica", "bold");
                        pdf.setFontSize(13);
                        pdf.text("Thesis Information", marginX, y);
                        y += 7;

                        pdf.setDrawColor(210);
                        pdf.line(marginX, y, pageWidth - marginX, y);
                        y += 7;

                        /* TITLE */
                        pdf.setFont("helvetica", "bold");
                        pdf.setFontSize(11);
                        pdf.text("Title:", marginX, y);

                        let wrapTitle = pdf.splitTextToSize(
                            "<?php echo htmlspecialchars($thesis['title']); ?>",
                            230
                        );

                        pdf.setFont("helvetica", "normal");
                        pdf.text(wrapTitle, marginX + 20, y);

                        y += wrapTitle.length * 6 + 4;
                        checkPageBreak();

                        /* AUTHORS */
                        pdf.setFont("helvetica", "bold");
                        pdf.text("Author(s):", marginX, y);

                        let wrapAuthor = pdf.splitTextToSize(
                            "<?php echo htmlspecialchars($thesis['author']); ?>",
                            230
                        );

                        pdf.setFont("helvetica", "normal");
                        pdf.text(wrapAuthor, marginX + 25, y);

                        y += wrapAuthor.length * 6 + 4;
                        checkPageBreak();

                        /* DEPARTMENT */
                        pdf.setFont("helvetica", "bold");
                        pdf.text("Department:", marginX, y);

                        pdf.setFont("helvetica", "normal");
                        pdf.text("<?php echo htmlspecialchars($thesis['department']); ?>", marginX + 30, y);

                        y += 8;
                        checkPageBreak();

                        /* YEAR */
                        pdf.setFont("helvetica", "bold");
                        pdf.text("Year:", marginX, y);

                        pdf.setFont("helvetica", "normal");
                        pdf.text("<?php echo htmlspecialchars($thesis['year']); ?>", marginX + 15, y);

                        y += 15;
                        checkPageBreak();


                        /* -----------------------------------------
                           SIGNATURE SECTION
                        ----------------------------------------- */
                        pdf.setDrawColor(180);
                        pdf.line(marginX + 5, y, marginX + 60, y);
                        pdf.line(pageWidth - 60, y, pageWidth - 5, y);

                        pdf.setFontSize(10);
                        pdf.text("Requested By", marginX + 18, y + 5);
                        pdf.text("Approved By", pageWidth - 47, y + 5);

                        y += 20;
                        checkPageBreak();

                        /* -----------------------------------------
                           FOOTER
                        ----------------------------------------- */

                        const datePrinted = new Date().toLocaleDateString("en-PH", {
                            year: "numeric",
                            month: "long",
                            day: "numeric",
                        });

                        pdf.setFontSize(9);
                        pdf.text(`Generated on ${datePrinted}`, marginX, pageHeight - 15);
                        pdf.text("PLV CEIT Thesis Hub", pageWidth - marginX, pageHeight - 15, {
                            align: "right",
                        });

                        /* SAVE */
                        pdf.save("Request_<?php echo $formatted_number; ?>.pdf");
                    };
                }




                // === 🖼️ Download as JPG (color accurate) ===
                async function downloadJPG() {
                    const element = document.querySelector(".confirmation-box");

                    // Apply computed styles for perfect color capture
                    applyComputedStyles(element);

                    const canvas = await html2canvas(element, {
                        scale: 3,
                        useCORS: true,
                        backgroundColor: window.getComputedStyle(element).backgroundColor
                    });

                    const link = document.createElement("a");
                    link.download = "Thesis_Request_<?php echo $formatted_number; ?>.jpg";
                    link.href = canvas.toDataURL("image/jpeg", 1.0);
                    link.click();
                }
            </script>



        </body>

        </html>
<?php
    } else {
        echo "<script>alert('Error submitting request: " . $conn->error . "'); window.location='catalog.php';</script>";
    }

    $conn->close();
}
?>