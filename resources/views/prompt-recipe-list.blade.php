@if(isset($result))
    <p>{{$result}}</p>
@else

    <div style="max-width:500px; margin:0 auto;">
        <h1>Creación de un listado de recetas provisionales</h1>
        <p>Este formulario permite crear un listado de nombres de recetas provisionales a partir de un contexto y un prompt.</p>
        <p>Ten en cuenta que puedes dejar los campos vacíos, en cuyo caso, se utilizará el contexto y prompt por defecto.</p>
        <p>Si on especificas el número de recetas, se generará una única receta</p>

        <hr>

        <form style="" action="{{route("postpromptrecipelist")}}" method="POST">
            {{csrf_field()}}
            <label style="display: block; margin-top: 25px;">
                <span>Contexto</span>
                <textarea autofocus style="width: 100%; height: 200px;" name="context"></textarea>
            </label>

            <label style="display: block; margin-top: 25px;">
                <span>Prompt</span>
                <textarea autofocus style="width: 100%; height: 200px;" name="prompt"></textarea>
            </label>

            <div style="display: flex; flex-direction: row; align-items: center; justify-content: space-between">
                <label style="display: flex; flex-direction: column; margin-top: 25px;">
                    <span>Número de recetas a generar</span>
                    <input type="number" name="num_recipes">
                </label>

                <label style="display: block; margin-top: 25px;">
                    <input type="submit" value="Encolar creación de recetas">
                </label>
            </div>
        </form>
    </div>
@endif
