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
    if (tab == null) $("#home").addClass("active");
    else
    {
        $("#"+tab).addClass("active");
        var children = $("ul[data-widget='tree']").children();
        var len = children.length;
        for (var i=0; i<len; i++)
        {
            var alt = children.eq(i).prop("data-alt"); // todo: menuuu....
            console.log(children.eq(i));
            if (alt.includes(tab))
            {
                alt.addClass("active");
                break;
            }
        }
        if ($("#"+tab).parent().parent().hasClass("treeview")) $("#"+tab).parent().parent().addClass("active");
    }
}

function acceptInvitation(teamid)
{
    $.get("/ajax/InvitationAccepted.php?teamid=" + teamid).done(function(data) {
        var obj = jQuery.parseJSON(JSON.stringify(data));
        if (obj.code === 1)
        {
            modalSuccess("Powodzenie", obj.msg);
            $("#inv" + teamid.toString()).remove();
            if ($.trim($("#invbox").html()).length == 0) $("#invbox").text("Nie jesteś aktualnie zaproszony do żadnej drużyny");
            if ($("#noteam_alert").length > 0)
            {
                $("#noteam_alert").remove();
                setTimeout(function() { window.location.reload(); }, 1000);
            }
        }
        else if (obj.code === 0)
        {
            modalWarning("Uwaga", obj.msg);
        }
        else if (obj.code === -1)
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
        if (obj.code === 1)
        {
            modalSuccess("Powodzenie", obj.msg);
            $("#inv" + teamid.toString()).remove();
            if ($.trim($("#invbox").html()).length == 0)
            {
                $("#invbox").text("Nie jesteś aktualnie zaproszony do żadnej drużyny");
            }
        }
        else if (obj.code === 0)
        {
            modalWarning("Uwaga", obj.msg);
        }
        else if (obj.code === -1)
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

        // adding player to additional info section
        var players_set = $("[name^='playername']");
        var player_credentials = $("[data-playerid=" + playerid.toString() + "]").text();
        players_set.append("<option value='" + playerid.toString() + "'>" + player_credentials + "</option>");
    }
    else
    {
        obj.removeClass("alert-success");
        obj.addClass("alert-info");
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

        // removing player from info section
        var players_set = $("[name^='playername']");
        players_set.children("[value=" + playerid.toString() + "]").remove();
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
       if (obj.code === -1)
       {
           modalError("Błąd", obj.msg);
       }
       else if (obj.code === 0)
       {
           modalWarning("Uwaga!", obj.msg);
       }
       else if (obj.code === 1)
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

function user_logout()
{
    $.get("/ajax/Logout.php").done(function(data){
        var obj = jQuery.parseJSON(JSON.stringify(data));
        if (obj.code === 1)
        {
            modalSuccess("Powodzenie", "Zostałeś poprawnie wylogowany!");
            setTimeout(logout2, 2000);
        }
        else
        {
            modalError("Błąd", "Problem z wylogowaniem")
        }
    });

    function logout2()
    {
        window.location.href = "/";
    }
}

function deleteTeam()
{
    var process = confirm("Czy na pewno chcesz usunąć drużynę ? Spowoduje to nieodwracalne usunięcie wszystkich powiązanych z nią danych!");
    if (!process) return;

    var teamid = team_selected;
    $.get("/ajax/DeleteTeam.php?teamid=" + teamid.toString()).done(function(data){
        var obj = jQuery.parseJSON(JSON.stringify(data));
        if (obj.code === -1)
        {
            modalError("Błąd", obj.msg);
        }
        else if (obj.code === 0)
        {
            modalWarning("Uwaga!", obj.msg);
        }
        else if (obj.code === 1)
        {
            modalSuccess("Powodzenie", obj.msg);
            $("#team" + teamid.toString()).remove();
            if ($("#teams_tbody").children().length === 1)
            {
                $("#teams_tbody").children().append("<div class='alert alert-info btn-block'>Nie jesteś w żadnej drużynie. Poproś swojego lidera o zaproszenie lub stwórz własną drużynę !</div>");
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
        if (obj.code === -1)
        {
            modalError("Błąd", obj.msg);
        }
        else if (obj.code === 0)
        {
            modalWarning("Uwaga!", obj.msg);
        }
        else if (obj.code === 1)
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

function defineSport()
{
    window.location.href = "index.php?tab=definesport&teamid=" + team_selected.toString();
}

function addGame()
{
    var teamid = getUrlParameter("teamid");
    window.location.href = "index.php?tab=games&minor=addgame&teamid=" + teamid.toString();
}

function checkLeadershipOld(teamid)
{
    $.get("/ajax/IsUserLeader.php?teamid=" + teamid.toString()).done(function(data) {
        var obj = jQuery.parseJSON(JSON.stringify(data));
        if (obj.code === -1)
        {
            window.localStorage.isLeader = -1;
            modalError("Błąd", obj.msg);
        }
        else if (obj.code === 0)
        {
            window.localStorage.isLeader = 0;
        }
        else if (obj.code === 1)
        {
            window.localStorage.isLeader = 1;
        }
    });
}

function checkLeadership(teamid)
{
    var btns = $("#teamLeaderBtns");
    $.get("/ajax/IsUserLeader.php?teamid=" + teamid.toString()).done(function(data) {
       var obj = jQuery.parseJSON(JSON.stringify(data));
       if (obj.code === -1)
       {
           modalError("Błąd", obj.msg);
       }
       else if (obj.code === 0)
       {
           btns.children().attr("disabled", "disabled");
       }
       else if (obj.code === 1)
       {
           btns.children().removeAttr("disabled");
       }
    });
}

function chooseTeam(id)
{
    var btns = $("#teamLeaderBtns");
    var real_id = "team" + id;
    var new_row = $("#" + real_id);
    var rows = $("#teams_tbody").children();
    team_selected = id;
    checkLeadership(id);
    rows.css("background-color", "#FFF");
    new_row.css("background-color", "#FFD700");
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