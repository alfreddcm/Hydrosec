<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Account</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        a{
            text-decoration: none;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #000;
        }
        .content {
            padding: 20px;
            max-width: 600px;
            margin: auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .button-group button {
            flex: 1;
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <a href="/Admin/dashboard">
                Dashboard
                </a>
                <a href="/Admin/profile">
                    | <strong>MANAGE ACCOUNT</strong>
                </a>
            </div>
            <div>
                <a href="#">LOG OUT</a>
            </div>
        </div>
        <div class="content">
            <a href="/admin/profile">

            </a>
            <h3 class="text-center">MANAGE ACCOUNT</h3>
            <form>
                <div class="form-group row">
                    <label for="username" class="col-sm-4 col-form-label">Username</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="username" value="Pats">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="firstName" class="col-sm-4 col-form-label">First Name</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="firstName" value="Patrick">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="lastName" class="col-sm-4 col-form-label">Last Name</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="lastName" value="Sabado">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="email" class="col-sm-4 col-form-label">Email</label>
                    <div class="col-sm-8">
                        <input type="email" class="form-control" id="email" value="pjm@gmail.com">
                    </div>
                </div>
                <div class="form-group row">
                                        <span>Password must be have atleast one uppercase and lowercase letter, number and a special character. </span>

                    <label for="password" class="col-sm-4 col-form-label">Password</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control" id="password" value="**********">
                    </div>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-outline-dark">CLOSE</button>
                    <button type="submit" class="btn btn-outline-dark">UPDATE</button>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-outline-dark">ADMIN LIST</button>
                    <button type="button" class="btn btn-outline-dark">DELETE ACCOUNT</button>
                </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
</body>
</html>
