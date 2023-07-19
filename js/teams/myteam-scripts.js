//////////////////////////
// Chiamate al database //
//////////////////////////


function fetchTeams() {
  jQuery.ajax({
    url: 'http://localhost/profile/myteam-helper.php?action=get-teams',
    type: 'GET',
    dataType: 'json',
    success: function (response) {

    },
    error: function (xhr, status, error) {
      console.log(xhr.responseText);
    }
  });
}

function fetchTeam(teamId) {

  jQuery.ajax({
    url: 'http://localhost/profile/myteam-helper.php?action=get-team',
    type: 'POST',
    data: { id: teamId },
    dataType: 'json',
    success: function (response) {
      if (response.status == 'success') {
        $.magnificPopup.close()
      }
    },
    error: function (xhr, status, error) {
      console.log(xhr.responseText);
    }
  });
}

function getSocietyByBoss(boss) {
  return new Promise(function(resolve, reject) {
      $.ajax({
          url: 'http://localhost/profile/myteam-helper.php',
          method: 'GET',
          data: {
              action: 'get-society-by-boss',
              boss: boss
          },
          dataType: "json",
          success: function(response) {
              updateMyStaffpage(response.society);
              resolve(response.society);
          },
          error: function(xhr, status, error) {
              reject(error);
          }
      });
  });
}


function getTeambyCoach(coach) {
  return new Promise(function (resolve, reject) {
    $.ajax({
      url: 'http://localhost/profile/myteam-helper.php?action=get-team-by-coach',
      method: 'GET',
      data: {
        coach: coach
      },
      dataType: "json",
      success: function (response) {
        updateMyTeampage(response.team);
        resolve(response.team.id);
      },
      error: function (xhr, status, error) {
        console.log(error);
        reject(error);
      }
    });
  });
}

function getPlayersbyTeam(team) {
  $.ajax({
    url: 'http://localhost/profile/myteam-helper.php?action=get-players-by-team',
    method: 'GET',
    data: {
      team: team
    },
    dataType: "json",
    success: function (response) {
      updateCardVisibility(response.players);
    },
    error: function (xhr, status, error) {
      console.log(error);
    }
  });
}

function getCoachesByBoss(mail) {
  $.ajax({
    url: 'http://localhost/profile/myteam-helper.php?action=get-coaches-by-boss',
    method: 'GET',
    data: {
      boss_email: mail
    },
    dataType: "json",
    success: function (response) {
      updateCardVisibility(response.coaches);
    },
    error: function (xhr, status, error) {
      console.log(error);
    }
  });
}

function updateMyTeampage(team) {
  var teamNameElement = document.getElementById("team-name");
  if (teamNameElement) {
    teamNameElement.textContent = team.nome;
  }

  var teamCodeElement = document.getElementById("team-code");
  if (teamCodeElement) {
    teamCodeElement.innerText = team.codice;
  }

  var hiddenTeamInput = document.getElementsByName("hidden-title-name")[0];
  if (hiddenTeamInput) {
    hiddenTeamInput.value = team.nome;
  }

  var hiddenCodeInput = document.getElementsByName("hidden-code")[0];
  if (hiddenCodeInput) {
    hiddenCodeInput.value = team.codice;
  }
}

function updateMyStaffpage(society) {
  var societyNameElement = document.getElementById("society-name");
  if (societyNameElement) {
    societyNameElement.textContent = society[0].nome;
  }

  var societyCodeElement = document.getElementById("society-code");
  if (societyCodeElement) {
    societyCodeElement.textContent = society[0].codice;
  }

  var hiddenTeamInput = document.getElementsByName("hidden-title-name")[0];
  if (hiddenTeamInput) {
    hiddenTeamInput.value = society[0].nome;
  }

  var hiddenCodeInput = document.getElementsByName("hidden-code")[0];
  if (hiddenCodeInput) {
    hiddenCodeInput.value = society[0].codice;
  }
}



function updateCardVisibility(players) {
  var n_players = 0;
  if (typeof players != 'undefined' && typeof players.length != 'undefined') {
    n_players = players.length;
  }

  var conditions = [];

  for (var i = 0; i < n_players; i++) {
    conditions.push(true);
  }

  var cards = document.querySelectorAll('.col-md-4');

  for (var i = 0; i < cards.length; i++) {
    if (conditions[i]) {
      var cardTitle = cards[i].querySelector('.card-title');
      var email = players[i].email;
      var cardImage = cards[i].querySelector('.card-img-top');

      cardTitle.textContent = email;
      cards[i].style.display = "block";
      cardImage.src = players[i].locazione_immagine_profilo;
    } else {
      cards[i].style.display = "none";
    }
  }
}


