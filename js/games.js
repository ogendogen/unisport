var game_choosen = -1;

function redirectAddGame()
{
    window.location.href = "index.php?tab=games&minor=addgame&teamid=" + getUrlParameter("teamid");
}

function redirectShowAll()
{
    window.location.href = "index.php?tab=games&minor=showall&teamid=" + getUrlParameter("teamid");
}

function redirectSummary()
{
    window.location.href = "index.php?tab=games&minor=summary&gameid=" + game_choosen.toString() + "&teamid=" + getUrlParameter("teamid");
}

function redirectEditGame()
{
    if (game_choosen === -1)
    {
        modalWarning("Uwaga!", "Wybierz mecz!");
        return;
    }
    window.location.href = "index.php?tab=games&minor=editgame&gameid=" + game_choosen.toString() + "&teamid=" + getUrlParameter("teamid");
}

function redirectExpert()
{
    window.location.href = "index.php?tab=games&minor=expert&teamid=" + getUrlParameter("teamid");
}

function redirectPDF()
{
    $.ajax({
        type: "GET",
        url: "/ajax/GetGamePDF.php?gameid=" + game_choosen.toString(),
        success: function(data, textStatus, request){

            var header = request.getResponseHeader("Content-Type");
            if (header === "application/pdf")
            {
                window.location.href = "/ajax/GetGamePDF.php?gameid=" + game_choosen.toString();
            }
            else if (header === "application/json")
            {
                var obj = jQuery.parseJSON(JSON.stringify(data));
                if (obj.code === 1)
                {
                    modalError("Błąd", obj.msg);
                }
                else if (obj.code === 0)
                {
                    modalWarning("Uwaga!", obj.msg);
                }
            }
            else
            {
                modalError("Błąd", "Nieznany typ odpowiedzi");
            }
        },
        error: function (request, textStatus, errorThrown) {
            modalError("Błąd", "Problem z połączeniem. Sprawdź swoje łącze!");
        }
    });
}

function checkLeadership_Games(teamid)
{
    var btns = $("#actionbuttons");
    $.get("/ajax/IsUserLeader.php?teamid=" + teamid.toString()).done(function(data) {
        var obj = jQuery.parseJSON(JSON.stringify(data));
        if (obj.code === -1)
        {
            modalError("Błąd", obj.msg);
        }
        else if (obj.code === 1)
        {
            btns.children()[0].disabled = false;
            btns.children()[1].disabled = false;
        }
    });
}

function chooseGame(gameid)
{
    var row = $("[data-gameid=" + gameid.toString() + "]");
    var btns = $("#actionbuttons").children();
    var rows = $("#tbody_games").children();

    rows.css("background-color", "#fff");
    game_choosen = gameid;
    checkLeadership_Games(getUrlParameter("teamid"));
    row.css("background-color", "#ffd700");

    btns.eq(2).removeAttr("disabled");
    btns.eq(3).removeAttr("disabled");

    /*var is_choosen_already = ($("[data-selected=1]").length > 0);

    if (row.attr("data-selected") == "1")
    {
        row.css("background-color", "#FFFFFF");
        row.attr("data-selected", "0");

        btns.eq(1).attr("disabled", "disabled");
        btns.eq(2).attr("disabled", "disabled");
        btns.eq(3).attr("disabled", "disabled");

        game_choosen = -1;
    }
    else if ((is_choosen_already && row.attr("data-selected") == "1") || (!is_choosen_already))
    {
        checkLeadership_Games(getUrlParameter("teamid"));
        btns.eq(2).removeAttr("disabled");
        btns.eq(3).removeAttr("disabled");
        //btns.eq(2).disabled = false;
        //btns.eq(3).disabled = false;

        row.css("background-color", "#FFD700");
        row.attr("data-selected", "1");
        game_choosen = gameid;
    }*/
}

function addNewGameAction()
{
    var obj = $("tr[id^='action']:last");
    var num = parseInt( obj.prop("id").match(/\d+/g), 10 ) +1;
    var cloned = obj.clone().prop("id", "action" + num.toString());
    cloned.children("td:nth-child(1)").children("select[name^='playername']").prop("name", "playername" + num.toString());
    cloned.children("td:nth-child(2)").children("select[name^='actionname']").prop("name", "actionname" + num.toString());
    cloned.children("td:nth-child(3)").children("input[name^='actionminute']").prop("name", "actionminute" + num.toString());
    cloned.children("td:nth-child(4)").children("input[name^='actionsecond']").prop("name", "actionsecond" + num.toString());
    obj.after(cloned);
}