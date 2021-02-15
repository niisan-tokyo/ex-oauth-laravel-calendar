<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
</head>
<body>
    <h1>TODO作成</h1>
    <form action="/todo" method="POST">
        @csrf
        タイトル<input name="summary" type="text" /> <br>
        開始<input name="start" type="text" /> <br>
        終了<input name="end" type="text" /> <br>
        <button type="submit">作成する</button>
    </form>
</body>
</html>