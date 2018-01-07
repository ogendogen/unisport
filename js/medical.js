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