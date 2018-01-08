<script src="../../../js/medical.js"></script>
<div class="row">
    <div class="col-md-3">
        <div class="box box-default">
            <div class="box-header with-border">
                <h4><i class="fa fa-plus"></i> Dodaj nowy wpis</h4>
            </div>
            <div class="box-body" onload="countParameters()">
                <form method="post">
                    <label for="height">Wysokość w cm</label>
                    <input type="number" class="btn-block" name="height" id="height" min="140" max="250" step="1" value="170" onchange="countParameters()">

                    <label for="weight">Waga w kg</label>
                    <input type="number" class="btn-block" name="weight" id="weight" min="30.00" max="200.00" step="1.00" value="80.00" onchange="countParameters()">

                    <label for="waist">Obwód pasa w cm</label>
                    <input type="number" class="btn-block" name="waist" id="waist" min="30.00" max="120.00" step="1.00" value="50.00" onchange="countParameters()">

                    <label for="state">Ogólny stan zdrowia</label>
                    <select class="btn-block" name="state" id="state">
                        <option value="injured">Skontuzjowany/Chory</option>
                        <option value="bad">Średni</option>
                        <option value="ok" selected>W porządku</option>
                        <option value="very fit">W bardzo dobrej formie</option>
                    </select>

                    <label for="iscapable">Czy zdolny do gry ?</label>
                    <select class="btn-block" name="iscapable" id="iscapable">
                        <option value="1" selected>Tak</option>
                        <option value="0">Nie</option>
                    </select>

                    <label for="bmi">BMI (obliczane automatycznie)</label>
                    <input type="number" class="btn-block" name="bmi" id="bmi" readonly="readonly">

                    <label for="fat">% tkanki tłuszczowej (obliczane automatycznie)</label>
                    <input type="number" class="btn-block" name="fat" id="fat" readonly="readonly">
                </form>
            </div>
        </div>
    </div>
</div>