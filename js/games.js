function redirectAddGame()
{
    window.location.href = "index.php?tab=games&minor=addgame&teamid=" + getUrlParameter("teamid");
}

function redirectShowAll()
{
    window.location.href = "index.php?tab=games&minor=showall&teamid=" + getUrlParameter("teamid");
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
    }
}

function addNewGameAction()
{
    var obj = $("tr[id^='action']:last");
    var num = parseInt( obj.prop("id").match(/\d+/g), 10 ) +1;
    var cloned = obj.clone().prop("id", "action" + num.toString());
    console.log(cloned);
    cloned.children("td:nth-child(1)").children("select[name^='playername']").prop("name", "playername" + num.toString());
    cloned.children("td:nth-child(2)").children("select[name^='actionname']").prop("name", "actionname" + num.toString());
    cloned.children("td:nth-child(3)").children("input[name^='actionminute']").prop("name", "actionminute" + num.toString());
    cloned.children("td:nth-child(4)").children("input[name^='actionsecond']").prop("name", "actionsecond" + num.toString());
    obj.after(cloned);
}