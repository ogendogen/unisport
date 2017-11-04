// Global variables

var team_selected = -1;
var actions_counter = 1;

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split("&"),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split("=");

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

function setActiveClass()
{
    var tab = getUrlParameter("tab");
    $("#"+tab).addClass("active");
    if ($("#"+tab).parent().parent().hasClass("treeview")) $("#"+tab).parent().parent().addClass("active");
}

function acceptInvitation(teamid)
{
    $.get("/ajax/InvitationAccepted.php?teamid=" + teamid).done(function(data) {
        var obj = jQuery.parseJSON(JSON.stringify(data));
        if (obj.code == "1")
        {
            modalSuccess("Powodzenie", obj.msg);
            $("#inv" + teamid.toString()).remove();
            if ($.trim($("#invbox").html()).length == 0)
            {
                $("#invbox").text("Nie jesteś aktualnie zaproszony do żadnej drużyny");
            }
        }
        else if (obj.code == "0")
        {
            modalWarning("Uwaga", obj.msg);
        }
        else if (obj.code == "-1")
        {
            modalError("Błąd", obj.msg);
        }
        else
        {
            modalWarning("Uwaga", "Nieznany kod");
        }
    });
}

function rejectInvitation(teamid)
{
    $.get("/ajax/RejectInvitation.php?teamid=" + teamid).done(function(data) {
        var obj = jQuery.parseJSON(JSON.stringify(data));
        if (obj.code == "1")
        {
            modalSuccess("Powodzenie", obj.msg);
            $("#inv" + teamid.toString()).remove();
            if ($.trim($("#invbox").html()).length == 0)
            {
                $("#invbox").text("Nie jesteś aktualnie zaproszony do żadnej drużyny");
            }
        }
        else if (obj.code == "0")
        {
            modalWarning("Uwaga", obj.msg);
        }
        else if (obj.code == "-1")
        {
            modalError("Błąd", obj.msg);
        }
        else
        {
            modalWarning("Uwaga", "Nieznany kod");
        }
    });
}

function chooseMember(memberid)
{
    var row = $("#" + memberid.toString());
    if (row.attr("data-selected") == "1")
    {
        $("#" + memberid.toString()).css("background-color", "#FFFFFF");
        $("#" + memberid.toString()).attr("data-selected", "0");
    }
    else
    {
        $("#" + memberid.toString()).css("background-color", "#FFD700");
        $("#" + memberid.toString()).attr("data-selected", "1");
    }
}

function chooseMemberToDelete(memberid)
{
    var row = $("[data-deleteid=" + memberid.toString() + "]");
    if (row.attr("data-selected") == "1")
    {
        row.css("background-color", "#FFFFFF");
        row.attr("data-selected", "0");
    }
    else
    {
        row.css("background-color", "#FFD700");
        row.attr("data-selected", "1");
    }
}

function redirectAddGame()
{
    window.location.href = "index.php?tab=games&minor=addgame&teamid=" + getUrlParameter("teamid");
}

function chooseGame(gameid)
{
    var row = $("[data-gameid=" + gameid.toString() + "]");
    var btns = $("#actionbuttons").children();
    if (row.attr("data-selected") == "1")
    {
        row.css("background-color", "#FFFFFF");
        row.attr("data-selected", "0");

        btns.each(function(){
            $(this).attr("disabled", "disabled");
        });
    }
    else
    {
        checkLeadership(getUrlParameter("teamid"));
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

function choosePlayer(playerid)
{
    var obj = $("[data-playerid=" + playerid.toString() + "]");
    var players_container = $("#players2sent");
    var choosen_players = players_container.children().length;
    if (obj.hasClass("alert-info"))
    {
        obj.removeClass("alert-info");
        obj.addClass("alert-success");
        choosen_players++;
        players_container.append("<input type='hidden' name='player" + choosen_players.toString() + "' value='" + playerid.toString() + "'>");
    }
    else
    {
        obj.removeClass("alert-success");
        obj.addClass("alert-info");
        console.log(choosen_players);
        if (choosen_players > 0) $("#players2sent [value='" + playerid.toString() + "']").remove();

        // reordering
        choosen_players = players_container.children().length;
        if (choosen_players == 0) players_container.children().empty();
        else if (choosen_players == 1) players_container.children().first().attr("name", "player1");
        else
        {
            var counter = 1;
            players_container.children().forEach(function(item, index){
                $(this).attr("name", "player" + counter.toString());
                counter++;
            });
        }
    }
}

function deleteLastGame()
{
    var lastgame = $("tr[id^='action']:last");
    if (lastgame.prop("id") === "action1")
    {
        modalWarning("Uwaga!", "Nie możesz usunąć ostatniego wiersza!");
        return;
    }
    lastgame.remove();
}

function sendInvitation()
{
    var teamid = getUrlParameter("teamid");
    var receivers = "";
    var choosen_rows = $("#foundmembers tbody tr[data-selected='1']");
    for (var i=0; i<choosen_rows.length; i++)
    {
        receivers = receivers.concat(choosen_rows.get(i).getAttribute("id"), "|");
    }
    if (receivers[receivers.length - 1] == "|") receivers = receivers.substring(0, receivers.length - 1);

    $.get("/ajax/SendInvitation.php?teamid=" + teamid.toString() + "&receivers=" + receivers).done(function(data){
       var obj = jQuery.parseJSON(JSON.stringify(data));
       if (obj.code == "-1")
       {
           modalError("Błąd", obj.msg);
       }
       else if (obj.code == "0")
       {
           modalWarning("Uwaga!", obj.msg);
       }
       else if (obj.code == "1")
       {
           modalSuccess("Powodzenie", obj.msg);
           choosen_rows.remove();
           if ($("#foundmembers tbody tr").length == 2) // dwa, ponieważ jakimś cudem generuje się jeszcze jeden pusty tr
           {
               $("#sendinv").replaceWith("<div class='alert alert-info text-center'>Wyszukaj zawodników wyżej</div>");
           }
       }
    });
}

function removeMember()
{
    var teamid = getUrlParameter("teamid");
    var kicked = "";
    var choosen_rows = $("#members2delete tbody tr[data-selected='1']");
    for (var i=0; i<choosen_rows.length; i++)
    {
        kicked = kicked.concat(choosen_rows.get(i).getAttribute("data-deleteid"), "|");
    }
    if (kicked[kicked.length - 1] == "|") kicked = kicked.substring(0, kicked.length - 1);

    $.get("/ajax/DeleteTeamMember.php?teamid=" + teamid.toString() + "&kicked=" + kicked).done(function(data){
        var obj = jQuery.parseJSON(JSON.stringify(data));
        if (obj.code == "-1")
        {
            modalError("Błąd", obj.msg);
        }
        else if (obj.code == "0")
        {
            modalWarning("Uwaga!", obj.msg);
        }
        else if (obj.code == "1")
        {
            modalSuccess("Powodzenie", obj.msg);
            choosen_rows.remove();
        }
    });
}

function editTeam()
{
    window.location.href = "index.php?tab=teamedit&teamid=" + team_selected.toString();
}

function editMembers()
{
    window.location.href = "index.php?tab=membersedit&teamid=" + team_selected.toString();
}

function addGame()
{
    var teamid = getUrlParameter("teamid");
    window.location.href = "index.php?tab=games&minor=addgame&teamid=" + teamid.toString();
}

function checkLeadership(teamid)
{
    $.get("/ajax/IsUserLeader.php?teamid=" + teamid.toString()).done(function(data) {
       var obj = jQuery.parseJSON(JSON.stringify(data));
       if (obj.code == "-1")
       {
           window.localStorage.isLeader = -1;
           modalError("Błąd", obj.msg);
       }
       else if (obj.code == "0")
       {
           window.localStorage.isLeader = 0;
       }
       else if (obj.code == "1")
       {
           window.localStorage.isLeader = 1;
       }
    });
}

function chooseTeam(id)
{
    var real_id = "team" + id;
    var row = $("#" + real_id);
    var btns = $("#teamLeaderBtns");
    if (team_selected === id) // kliknięcie w zaznaczone
    {
        team_selected = -1;
        row.css("background-color", "#FFFFFF");
        btns.children().prop("disabled", true);
    }
    else if (team_selected === -1) // zaznaczył nową drużynę
    {
        team_selected = id;
        row.css("background-color", "#FFD700");
        checkLeadership(team_selected);
        if (window.localStorage.isLeader == "1") btns.children().prop("disabled", false);
    }
    // w innym wypadku ignoruj - prawdopodobnie próbuje zaznaczyć dwie drużyny
}

function modalSuccess(title, msg)
{
    $("#myModal .modal-dialog .modal-content .modal-title").html("<span style=\"font-weight: bold;\">" + title + "</span>");
    $("#myModal .modal-dialog .modal-body").html("<div class=\"alert alert-success\"><span style=\"font-weight: bold;\">" + msg + "</span></div>");
    $("#myModal").modal("show");
}

function modalWarning(title, msg)
{
    $("#myModal .modal-dialog .modal-content .modal-title").html("<span style=\"font-weight: bold;\">" + title + "</span>");
    $("#myModal .modal-dialog .modal-body").html("<div class=\"alert alert-warning\"><span style=\"font-weight: bold;\">" + msg + "</span></div>");
    $("#myModal").modal("show");
}

function modalError(title, msg)
{
    $("#myModal .modal-dialog .modal-content .modal-title").html("<span style=\"font-weight: bold;\">" + title + "</span>");
    $("#myModal .modal-dialog .modal-body").html("<div class=\"alert alert-danger\"><span style=\"font-weight: bold;\">" + msg + "</span></div>");
    $("#myModal").modal("show");
}