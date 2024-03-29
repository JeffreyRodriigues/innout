<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/comum.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/icofont.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <title>Entra e sai</title>
</head>

<body class="hide-sidebar">
    <form class="form-login" action="#" method="post">
        <div class="login-card card">
            <div class="card-header">
                <i class="icofont-travelling mr-3"></i>
                <span class="font-weight-light">Entra</span>
                <span class="font-weight-bold mx-2"> e</span>
                <span class="font-weight-light">Sai</span>
                <i class="icofont-runner-alt-1 ml-3"></i>
            </div>
            <div class="card-body">
                <?php include(TEMPLATE_PATH) . '/messages.php'?>
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" 
                    class="form-control <?= $errors['email'] ? 'is-invalid' : '' ?>" 
                    value="<?= $email ?>"
                    placeholder="Informe o e-mail" autofocus>
                    <div class="invalid-feedback">
                        <?= $errors['email'] ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" 
                    class="form-control <?= $errors['password'] ? 'is-invalid' : '' ?>" 
                    placeholder="Informe a senha">
                    <div class="invalid-feedback">
                        <?= $errors['password'] ?>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-lg btn-dark">Entrar</button>
            </div>
        </div>
    </form>
</body>
</html>