function redirectEditSport(sportid)
{
    window.location.href = "?tab=definesport_edit&sportid=" + sportid.toString();
}

function sportBack()
{
    window.location.href = "?tab=definesport&teamid=-1";
}

function addNewSportAction()
{
    var actions = $("#sport_actions");
    var children = actions.children();
    var last = actions.children().last();
    var cloned = last.clone().prop("name", "actions[]").prop("id", "action" + (++children.length).toString()).prop("maxlength", "32");
    actions.append(cloned);
}

function deleteLastSportAction()
{
    var actions = $("#sport_actions");
    var children = actions.children();
    if (children.length > 1) children.last().remove();
    else modalWarning("Uwaga", "Nie możesz usunąć ostatniej akcji!");
}