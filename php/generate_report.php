<?php
session_start();
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] !== 'Finance') {
    header("Location: ../loginPage.php");
    exit();
}
require_once "tcpdf/tcpdf.php";
require_once "models/DatabaseManager.php";

$conn = DatabaseManager::getInstance()->getConnection();

$currentEmployeeId = $_SESSION['employeeId'];

$sql = "SELECT ec.*, e.firstName, e.lastName 
        FROM expense_claims ec
        INNER JOIN employees e ON ec.employeeId = e.employeeId
        WHERE ec.status IN ('Reimbursed')
          AND e.manager = (
              SELECT manager
              FROM employees
              WHERE employeeId = :currentEmployeeId
          )";

$params = [':currentEmployeeId' => $currentEmployeeId];


$sql .= " ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$claims = $stmt->fetchAll(PDO::FETCH_ASSOC);


// pdf creation
$pdf = new TCPDF();
$pdf->SetCreator('FDM Expense App');
$pdf->SetAuthor('Finance System');
$pdf->SetTitle('Reimbursed Claims Report');
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

$pdf->Write(0, "Reimbursed Claims Report", '', 0, 'C', true, 0, false, false, 0);
$pdf->Ln(4);

$html = '<table border="1" cellpadding="4">
            <thead>
                <tr style="font-weight:bold; background-color:#eee;">
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Currency</th>
                </tr>
            </thead><tbody>';

foreach ($claims as $claim) {
    $html .= "<tr>
                <td>{$claim['claimId']}</td>
                <td>{$claim['firstName']} {$claim['lastName']}</td>
                <td>{$claim['amount']}</td>
                <td>{$claim['category']}</td>
                <td>{$claim['description']}</td>
                <td>" . date('d M Y', strtotime($claim['date'])) . "</td>
                <td>{$claim['currency']}</td>
              </tr>";
}

$html .= "</tbody></table>";
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->Output('Reimbursed_Claims_Report.pdf', 'I');
?>
