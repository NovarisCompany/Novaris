<html>

<head>
  <title>Problema</title>
</head>

<body>

  <?php
  $conexion = mysqli_connect("localhost", "root", "", "sistema_gestion_ti") or
    die("Problemas con la conexión");

  $registros = mysqli_query($conexion, "SELECT * FROM usuario") or
    die("Problemas en el select: " . mysqli_error($conexion));

  $tickets = mysqli_fetch_all($registros, MYSQLI_ASSOC);

  mysqli_close($conexion);
  ?>
  <table class="ticket-table2">
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>Mail</th>
        <th>Telefono</th>
        <th>Estado Cuenta</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tickets as $ticket): ?>
        <tr>
          <td><?php echo htmlspecialchars($ticket['nombre']); ?></td>
          <td><?php echo htmlspecialchars($ticket['apellido']); ?></td>
          <td><?php echo htmlspecialchars($ticket['email']); ?></td>
          <td><?php echo htmlspecialchars($ticket['telefono']); ?></td>
          <td><?php echo htmlspecialchars($ticket['estado_cuenta']); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
<style>
.ticket-table2{
    background: #ffffff;
    padding: 108px;
    border-radius: 10px;
    box-shadow: 0 18px 36px rgba(3,29,69,0.08);
    color: #030736;
    margin-left: 320px;

}
</style>

</html>