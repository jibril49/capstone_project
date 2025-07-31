<?php
include 'config.php';

$sql = "SELECT * FROM testimonials";
$result = $conn->query($sql);

$testimonials = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $testimonials[] = $row;
    }
}

echo json_encode($testimonials);

$conn->close();
?>