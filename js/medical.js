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

function countBMI(height, weight)
{
    var num = weight / (height * height) * 10000;
    return Math.round(num * 100) / 100;
}

function countFat(height, weight, waist)
{
    var a = 4.15 * waist;
    var b = a / 2.54;
    var c = 0.082 * weight * 2.2;
    var d = b - c - 98.42;
    var e = weight * 2.2;
    var num = d/e * 100;

    return Math.round(num * 100) / 100;
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
    var result = $("#bmiresult");

    result.removeClass();
    result.addClass("btn-block");

    if (bmi < 16.99)
    {
        result.text("Wychudzenie");
        result.addClass("alert-danger");
    }
    else if (bmi < 18.5)
    {
        result.text("Niedowaga");
        result.addClass("alert-warning");
    }
    else if (bmi < 24.99)
    {
        result.text("Waga prawidłowa");
        result.addClass("alert-success");
    }
    else if (bmi < 29.99)
    {
        result.text("Nadwaga");
        result.addClass("alert-warning");
    }
    else if (bmi > 30.00)
    {
        result.text("Otyłość");
        result.addClass("alert-danger");
    }

    $("#bmi").val(bmi);
    $("#fat").val(fat);
}