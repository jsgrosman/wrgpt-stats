import $ from 'jquery';
import Chart from 'chart.js';
import 'chartjs-plugin-labels';
import 'bootstrap';
import 'chosen-js'

import 'chosen-js/chosen.min.css';
import 'bootstrap/dist/css/bootstrap.min.css';

var ctx = document.getElementById("activeTimesChart").getContext('2d');
var activeTimesChart = new Chart(ctx, {
    type: 'radar',
    data : {
        labels: [
            '0:00',
            '1:00',
            '2:00',
            '3:00',
            '4:00',
            '5:00',
            '6:00',
            '7:00',
            '8:00',
            '9:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00',
            '18:00',
            '19:00',
            '20:00',
            '21:00',
            '22:00',
            '23:00',
        ],
        datasets: []
    },
});

const query = new URLSearchParams(location.search);

const players = query.get('players').split(',');

let colors = [
    "rgba(100,0,0,0.2)",
    "rgba(200,0,0,0.2)",
    "rgba(0,100,0,0.2)",
    "rgba(0,200,0,0.2)",
    "rgba(0,0,100,0.2)",
    "rgba(100,0,100,0.2)",
    "rgba(100,0,200,0.2)",
    "rgba(200,0,100,0.2)",
    "rgba(200,0,200,0.2)",
    "rgba(100,100,0,0.2)",
    "rgba(100,200,0,0.2)",
    "rgba(200,100,0,0.2)",
    "rgba(200,200,0,0.2)",
];

players.forEach(function(player) {
    fetch('/players/' + player).then(function(res) {
        return res.json();
    }).then(function(playerObj) {

        $('#playerStats').append("<tr><td><a href='player.html?player=" + playerObj.name + "'>" + playerObj.name + "</a></td>" +
            "<td>" + playerObj.flopPct + "</td>" +
            "<td>" + playerObj.vpip + "</td>" +
            "<td>" + playerObj.pfr + "</td>" +
            "<td>" + playerObj.aggressionFactor + "</td>" +
            "<td>" + playerObj.wentToShowdownPct + "</td>" +
            "</tr>");

        activeTimesChart.data.datasets.push(
            {
                label: playerObj.name,
                backgroundColor: colors.pop(),
                data: Object.values(playerObj.hours)    ,
            }
        );
        activeTimesChart.update();
    });
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
                total += actionsObject[actionType][key];
                break;
        }
    });

    return parseFloat((folds/total) * 100).toFixed(2)+"%";
}

fetch('/players').then(function(res) {
    return res.json();
}).then(function(playerObj) {


    var itemsProcessed = 0;
    playerObj.forEach(function(name) {
        $('#allPlayers').append($('<option>', { value : name })
            .text(name));

        itemsProcessed++;
        if(itemsProcessed === playerObj.length) {

            $('#allPlayers').val(players).chosen();
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
