@isset($result)
    <p>{{$result}}</p>
@endisset

@isset($recetas)
    Recetas generadas:
    <ul>
    @foreach($recetas as $receta)
        <li>{{$receta}}</li>
    @endforeach
    </ul>
@endisset

@isset($recetasAInsertar)
    Recetas a insertar:
    <ul>
        @foreach($recetasAInsertar as $receta)
            <li>{{$receta}}</li>
        @endforeach
    </ul>
@endisset

<form action="{{route("postpromptrecipelist")}}" method="POST">
    {{csrf_field()}}
    <fieldset>
        <textarea autofocus style="width: 100%; height: 200px;" name="context"></textarea>
    </fieldset>

    <fieldset>
        <textarea autofocus style="width: 100%; height: 200px;" name="prompt"></textarea>
    </fieldset>

    <fieldset>
        <input type="number" name="num_recipes" placeholder="Enter number of recipes">
    </fieldset>
    <fieldset>
        <input type="submit" value="Submit">
    </fieldset>
</form>
