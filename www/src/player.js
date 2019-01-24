import $ from 'jquery';
import Chart from 'chart.js';
import 'chartjs-plugin-labels';
import 'bootstrap';
import 'chosen-js'

const query = new URLSearchParams(location.search);

fetch('player.php?player=' + query.get('player')).then(function(res) {
    return res.json();
}).then(function(playerObj) {
    console.log(playerObj);
    console.log(playerObj.hours);

    document.getElementById('firstAction').innerText = playerObj.firstAction;
    document.getElementById('lastAction').innerText = playerObj.lastAction;
    document.getElementById('minMult').innerText = playerObj.multipliers['minMult'];
    document.getElementById('maxMult').innerText = playerObj.multipliers['maxMult'];
    document.getElementById('avgMult').innerText = playerObj.multipliers['avgMult'];
    document.getElementById('playerTournaments').innerText = playerObj.tournaments.join(', ');
    document.getElementById('playerTables').innerText = playerObj.tables.join(', ');
    document.getElementById('playerCards').innerHTML = playerObj.cards.join(', ');
    document.getElementById('allPlayers').value = playerObj.name;
    $('#allPlayers').trigger("chosen:updated");


    // document.getElementById('allFoldPct').innerText = getFoldPercentage(playerObj.actions, 'all');
    // document.getElementById('preflopFoldPct').innerText = getFoldPercentage(playerObj.actions, 'preflop');
    // document.getElementById('flopFoldPct').innerText = getFoldPercentage(playerObj.actions, 'flop');
    // document.getElementById('turnFoldPct').innerText = getFoldPercentage(playerObj.actions, 'turn');
    // document.getElementById('riverFoldPct').innerText = getFoldPercentage(playerObj.actions, 'river');


    document.getElementById('pctFlop').innerText = playerObj.flopPct;
    document.getElementById('vpip').innerText = playerObj.vpip;
    document.getElementById('pfr').innerText = playerObj.pfr;
    document.getElementById('classification').innerText = playerObj.classification;
    document.getElementById('cardRange').innerText = playerObj.range;
    document.getElementById('aggFactor').innerText = playerObj.aggressionFactor;
    document.getElementById('wtsd').innerText = playerObj.wentToShowdownPct;
    document.getElementById('preflopRoundCount').innerText = playerObj.rounds['preflop'];
    document.getElementById('flopRoundCount').innerText = playerObj.rounds['flop'];
    document.getElementById('turnRoundCount').innerText = playerObj.rounds['turn'];
    document.getElementById('riverRoundCount').innerText = playerObj.rounds['river'];

    var ctx = document.getElementById("activeTimesChart").getContext('2d');
    var activeTimesChart = new Chart(ctx, {
        type: 'radar',
        data : {
            labels: Object.keys(playerObj.hours),
            datasets: [{
                label: playerObj.name,
                backgroundColor: "rgba(200,0,0,0.2)",
                data: Object.values(playerObj.hours)    ,
            }]
        },
    });

    for (var i = 1; i < 10; i++)
    {
        document.getElementById('vpip_' + i).innerText = playerObj.position[i].vpip;
        document.getElementById('pfr_' + i).innerText = playerObj.position[i].pfr;
        document.getElementById('classification_' + i).innerText = playerObj.position[i].classification;
        document.getElementById('cardRange_' + i).innerText = playerObj.position[i].range;
    }

        // displayActionsChart(playerObj.actions, 'all', 'allActionsChart');
        // displayActionsChart(playerObj.actions, 'preflop', 'preflopActionsChart');
        // displayActionsChart(playerObj.actions, 'flop', 'flopActionsChart');
        // displayActionsChart(playerObj.actions, 'turn', 'turnActionsChart');
        // displayActionsChart(playerObj.actions, 'river', 'riverActionsChart');
});

function getFoldPercentage(actionsObject, actionType)
{
    var folds = 0;
    var total = 0;
    Object.keys(actionsObject[actionType]).forEach(function(key) {
        switch (key) {
            case 'folds':
                folds += actionsObject[actionType][key];
            case 'checks':
            case 'bets':
            case 'calls':
            case 'raises':
            case 'reraises':
                total += actionsObject[actionType][key];
                break;
        }
    });

    return parseFloat((folds/total) * 100).toFixed(2)+"%";
}

function displayActionsChart(actionsObject, actionType, ctxId) {
    var ctx = document.getElementById(ctxId).getContext('2d');
    var actionsChart = new Chart(ctx, {
        type: 'doughnut',
        plugins: {
            labels: [
                {
                    render: 'label',
                    position: 'outside'
                },
                {
                    render: 'percentage',
                    precision: 2
                },

            ]
        },
        data : {
            labels: Object.keys(actionsObject[actionType]),
            datasets: [{
                label: "jasong",
                backgroundColor: [
                    "rgba(100,0,0,0.2)",
                    "rgba(200,0,0,0.2)",
                    "rgba(0,100,0,0.2)",
                    "rgba(0,200,0,0.2)",
                    "rgba(0,0,100,0.2)",
                    "rgba(100,0,100,0.2)",
                ],
                data: Object.values(actionsObject[actionType]),
            }]
        },
    });
};

fetch('allPlayers.php').then(function(res) {
    return res.json();
}).then(function(playerObj) {


    var itemsProcessed = 0;
    playerObj.forEach(function(name) {
        $('#allPlayers').append($('<option>', { value : name })
            .text(name));

        itemsProcessed++;
        if(itemsProcessed === playerObj.length) {
            $('#allPlayers').chosen();
        }
    });



});

$('#playerSearchForm').on('submit', function(e) {
    e.preventDefault();

    var selMulti = $.map($("#allPlayers option:selected"), function (el, i) {
        return $(el).text();
    });

    if (selMulti.length > 1) {
        window.location = 'table.html?players=' + selMulti.join(",");
    }
    else {
        window.location = 'player.html?player=' + selMulti[0];
    }
});
