<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary Generator</title>
</head>
<body>
    <h1>Summary Generator</h1>
    <form action="" method="POST">
        @csrf
        <input type="text" name="url">
        <input type="submit">
    </form>
    <div>
    @isset($summary)
    <p>{{$summary}}</p>
    @endisset
    @isset($error)
    <p>{{$error}}</p>
    @endisset
    </div>
</body>
</html>