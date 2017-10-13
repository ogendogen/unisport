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