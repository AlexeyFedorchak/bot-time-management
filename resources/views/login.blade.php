<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

<div class="alert alert-primary" role="alert" style="text-align: center">
    Авторизація в системі менеджменту робочого часу.
</div>
<div style="    display: flex;
    align-items: center;
    width: 100%;
    justify-content: center;">
    <form action="{{ route('login') }}" method="post">
        @csrf
        {{--    <label for="name">Name:</label>--}}
        <input id="name" type="text" name="email" placeholder="email">

        <br>
        <br>
        {{--    <label for="password">Password:</label>--}}
        <input id="password" type="password" name="password" placeholder="password">
        <br>
        <br>
        <input type="submit" value="Увійти">
    </form>
</div>

