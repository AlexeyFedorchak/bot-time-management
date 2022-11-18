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
    <input type="submit" value="Save">
</form>
