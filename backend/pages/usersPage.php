<?php
/**
 * Esta página es temporal, ya que del front-end se encarga REACT
 */
    require_once __DIR__.'/../functions/adminFunctions.php';
    require_once __DIR__.'/../functions/commonFunctions.php';        

    iniciarSesionSiNoActiva();

    $tableContent = "";

    if (isset($_SESSION["role"])) {
        $users = getUsersData();

        if (is_array($users) && !empty($users)) {

            foreach ($users as $user) {
                $tableContent .= "<tr>";

                foreach ($user as $column) {
                    $tableContent .= "<td>$column</td>";
                }

                $tableContent .= "</tr>";
            }

        } else {
            $tableContent = "<tr><td colspan='4'>No hay datos</td></tr>";
        }
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../.icons/iconoPHP.png"> <!-- Esta línea hay que borrarla en producción -->
    <title>Users (backend)</title>
</head>
<body>
    <h1>Esta es la página de acciones del usuario</h1>
    <br>
    <form action="../functions/userFunctions/logOut.php">
        <button type="submit">Cerrar Sesión</button>
    </form>
    <br>          
    <h2>Sign Up</h2>
    <form action="../functions/userFunctions/signUp.php" method="POST">
        <label for="">Username</label>
        <input type="text" name="username">
        <label for="">email</label>
        <input type="text" name="email">
        <label for="">password</label>
        <input type="text" name="password">
        <button type="submit">Sign up</button>
    </form>
    <br>
    <h2>Login</h2>
    <form action="../functions/userFunctions/logIn.php" method="POST">
        <label for="">Username or Email</label>
        <input type="text" name="username">
        <label for="">password</label>
        <input type="text" name="password">
        <button type="submit">Sign in</button>
    </form>
    <br>
    <h2>Users Data</h2>
    <span>Sólo lo verás si eres admin</span>
    <table>
        <thead>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Password</th>
            <th>Created at</th>
        </thead>
        <tbody>
            <?= $tableContent ?>
        </tbody>
    </table>


</body>
</html>