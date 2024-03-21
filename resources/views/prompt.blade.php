<form action="{{route("postprompt")}}" method="POST">
    <fieldset>
        <textarea autofocus style="width: 100%; height: 400px;" name="prompt"></textarea>
    </fieldset>

    <fieldset>
        <input type="number" name="num_recipes" placeholder="Enter number of recipes">
    </fieldset>
    <fieldset>
        <input type="submit" value="Submit">
    </fieldset>
</form>
