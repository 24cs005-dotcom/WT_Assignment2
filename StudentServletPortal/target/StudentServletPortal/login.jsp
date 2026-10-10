<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal Login</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/style.css">
</head>
<body>

<div class="login-container">

    <h2>Student Portal Login</h2>

    <form action="${pageContext.request.contextPath}/login" method="post">

        <div class="form-group">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                placeholder="Enter username"
                required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter password"
                required>
        </div>

        <button type="submit">Login</button>

    </form>

</div>

</body>
</html>