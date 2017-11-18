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
    window.location.href = "index.php?tab=games&minor=editgame&gameid=" + game_choosen.toString();
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
                if (obj.code == "-1")
                {
                    modalError("Błąd", obj.msg);
                }
                else if (obj.code == "0")
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

function chooseGame(gameid)
{
    var row = $("[data-gameid=" + gameid.toString() + "]");
    var btns = $("#actionbuttons").children();
    var is_choosen_already = ($("[data-selected=1]").length > 0);
    checkLeadership(getUrlParameter("teamid"));
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
        if (window.localStorage.isLeader == 1)
        {
            btns.each(function(){
                $(this).removeAttr("disabled");
            });
        }
        else if (window.localStorage.isLeader == 0)
        {
            btns.eq(2).removeAttr("disabled");
            btns.eq(3).removeAttr("disabled");
            btns.eq(4).removeAttr("disabled");
        }
        else
        {
            modalError("Błąd", "Problem z autoryzacją lidera. Sprawdź połączenie internetowe lub przeloguj się");
        }
        row.css("background-color", "#FFD700");
        row.attr("data-selected", "1");
        game_choosen = gameid;
    }
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