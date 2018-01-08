function getPlayersList(team)
{
    var teamid = team.value;
    var players = $("#players");
    players.prop("disabled", "disabled");
    $.get("/ajax/GetTeamPlayers.php?teamid=" + teamid.toString()).done(function(data) {
        var obj = jQuery.parseJSON(JSON.stringify(data));
        if (obj.code === -1)
        {
            modalError("Błąd", obj.msg);
        }
        else if (obj.code === 1)
        {
            players.empty();
            var len = obj.arr.length;
            if (len === 0)
            {
                players.append("<option disabled selected>Brak zawodników!</option>");
            }
            else
            {
                for (var i=0; i<len; i++)
                {
                    players.append("<option value='" + obj.arr[i]["user_id"] + "'>" + obj.arr[i]["user_name"] + " " + obj.arr[i]["user_surname"] + "</option>");
                }
            }
            players.removeAttr("disabled");
        }
    });
}

/*
        private function countBMI(int $height, float $weight) : float
        {
            $f_height = floatval($height);
            $f_height = round($height / 100, 2);

            return round($weight / ($f_height * $f_height), 2);
        }

        private function countFat(float $weight, float $waist) : float
        {
            $a = 4.15 * $waist;
            $b = $a / 2.54;
            $c = 0.082 * $weight * 2.2;
            $d = $b - $c - 98.42;
            $e = $weight * 2.2;

            return round($d/$e * 100, 2);
        }

 */

function countBMI(height, weight)
{
    return weight / (height * height);
}

function countFat(height, weight, waist)
{
    var a = 4.15 * waist;
    var b = a / 2.54;
    var c = 0.082 * weight * 2.2;
    var d = b - c - 98.42;
    var e = weight * 2.2;

    return d/e * 100;
}

function countParameters()
{
    var height = $("#height").val();
    var weight = $("#weight").val();
    var waist = $("#waist").val();

    if (height === "" || height == null)
    {
        $("#bmi").prop("readonly", false).prop("placeholder", "Brak wysokości").prop("readonly", true);
        $("#fat").prop("readonly", false).prop("placeholder", "Brak wysokości").prop("readonly", true);
        return;
    }
    else if (weight === "" || weight == null)
    {
        $("#bmi").prop("readonly", false).prop("placeholder", "Brak wagi").prop("readonly", true);
        $("#fat").prop("readonly", false).prop("placeholder", "Brak wagi").prop("readonly", true);
        return;
    }
    else if (waist === "" || waist == null)
    {
        $("#bmi").prop("readonly", false).prop("placeholder", "Brak obwodu w pasie").prop("readonly", true);
        $("#fat").prop("readonly", false).prop("placeholder", "Brak obwodu w pasie").prop("readonly", true);
        return;
    }

    var bmi = countBMI(height, weight);
    var fat = countFat(height, weight, waist);

    $("#bmi").val(bmi);
    $("#fat").val(fat);
}