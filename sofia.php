<?php 
header('Content-type: text/html; charset=utf-8'); 
?>

<?php

// Cria a conexão
$con=mysqli_connect("localhost","gloss354_php","riso1903","gloss354_prst6");

// Check connection
if (mysqli_connect_errno($con))
  {
  exit("Failed to connect to MySQL: " . mysqli_connect_error());
  }

echo "Bem vinda às estatísticas !<br>";

$result = mysqli_query($con,"
				SELECT `time_start`
				FROM  `ps_date_range` 
				ORDER BY  `ps_date_range`.`time_start` DESC 
				LIMIT 7
			");

while($row = mysqli_fetch_array($result))
  {
  echo date("d/m/Y", strtotime($row['time_start'])) . " : ";
  echo "<br>";
  }




?>




<?php
echo "Fim de papo";
// Encerra a sessão
mysqli_close($con);
?>