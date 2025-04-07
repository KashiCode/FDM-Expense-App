<?php
require_once "tcpdf/tcpdf.php";
require_once "models/DatabaseManager.php";

$conn = DatabaseManager::getInstance()->getConnection();

$sql = "SELECT ec.*, e.firstName, e.lastName FROM expense_claims ec JOIN employees e ON ec.employeeID = e.employeeID WHERE ec.status = 'Reimbursed' ORDER BY ec.date DESC";

$statement = $conn->prepare($sql);
$statement->execute();
$claims = $statement->fetchAll(PDO::FETCH_ASSOC);


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
