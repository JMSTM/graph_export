async function loadDatas(site_id, day_range = false, type = 'delay')
{
    const urlPrefix = {
        delay: './datas.php',
        metrics: './datasMetric.php',
    };

    let params = "?site_id=" + site_id;

    if (day_range !== false)
    {
        params += "&day_range=" + day_range;
    }
    if (type == "metrics")
    {
        params += '&metric_id=1';
    }

    const url = urlPrefix[type]+params;
    // console.log(type,  urlPrefix, urlPrefix[type], url);

    const data = await fetch(url);
    const dataJson = await data.json();

    return new Promise((resolve, reject) =>
    {
        // console.log(dataJson);
        resolve(dataJson);
    });
}

// import metrics from './datasMetric.php?metric_id=1';

async function getRealTime()
{
    const dataSetProdDelay = await loadDatas(2, 0)
    const dataSetProdJobs = await loadDatas(2, 0, 'metrics');

    return {
        prodDelay: dataSetProdDelay,
        prodJobs: dataSetProdJobs,
    };
}


async function makeGraph()
{
    // Prod delay
    loadDatas(2)
        .then(dataSet =>
        {

            // let datasets = graphs.prod.data.datasets;
            let colors = ["#777777", "#ff0000", "#ff6600", "#ba6900",
                "#2a8f0f", "#000000"];
            let first = true;
            Object.keys(dataSet).forEach(keyDay =>
            {
                graphs.prod.data.datasets.push({
                    lineTension: 0,
                    day: keyDay,
                    datatype:'prodDelay',
                    label: "Delay " + keyDay,
                    borderColor: colors.pop(),
                    data: dataSet[keyDay],
                    fill: false,
                    borderWidth: 1,
                    pointRadius: 0,
                    yAxisID: 'left',
                    // steppedLine: true,
                    hidden: !first,
                });
                first = false;
            });
            // graphs.prod.data.datasets = datasets;
            graphs.prod.update();
        });

    //Prod jobs
    loadDatas(2, false, 'metrics').then((metrics) =>
    { //Prod
        // let datasets = graphs.prod.data.datasets;
        // console.log(metrics);
        let colors = ["#777777", "#ff0000", "#ff6600", "#ba6900",
            "#2a8f0f"];
        let backgroundColors = ["rgba(42, 143, 15, 0.25)"];
        let first = true;
        Object.keys(metrics).forEach(keyDay =>
        {
            graphs.prod.data.datasets.push({
                lineTension: 0,
                day: keyDay,
                datatype:'prodJobs',
                label: "Jobs" + keyDay,
                steppedLine: true,
                borderColor: colors.pop(),
                backgroundColor: "rgba(42, 143, 15, 0.25)",
                data: metrics[keyDay],
                fill: true,
                borderWidth: 1,
                pointRadius: 0,
                yAxisID: 'right',
                hidden: !first,
            });
            first = false;
        });

        // graphs.prod.data.datasets = datasets;
        graphs.prod.update();
    });

    loadDatas(1) //Qa
        .then(dataSet =>
        {

            let datasetsQA = [];
            let colors = ["#ff0000", "#ff6600", "#ba6900", "#2a8f0f", "#000000"];
            Object.keys(dataSet).forEach(keyDay =>
            {
                datasetsQA.push({
                    lineTension: 0,
                    day: keyDay,
                    label: keyDay,
                    borderColor: colors.pop(),
                    data: dataSet[keyDay],
                    fill: false,
                    borderWidth: 1,
                    pointRadius: 0,
                });
            });

            graphs.qa.data.datasets = datasetsQA;
            graphs.qa.update();
        });


}

function refreshGraph()
{
    const values = $("#slider-range").slider("values");
    graphs.prod.options.scales.xAxes[0].ticks.min =
        moment().format("YYYY-MM-DD") + " "
        + Math.floor(values[0] / 60).toString().padStart(2, '0') + ":"
        + (values[0] % 60).toString().padStart(2, '0') + ":00";

    graphs.prod.options.scales.xAxes[0].ticks.max =
        moment().format("YYYY-MM-DD") + " "
        + Math.floor(values[1] / 60).toString().padStart(2, '0') + ":"
        + (values[1] % 60).toString().padStart(2, '0') + ":00";

    graphs.qa.options.scales.xAxes[0].ticks.min =
        moment().format("YYYY-MM-DD") + " "
        + Math.floor(values[0] / 60).toString().padStart(2, '0') + ":"
        + (values[0] % 60).toString().padStart(2, '0') + ":00";

    graphs.qa.options.scales.xAxes[0].ticks.max =
        moment().format("YYYY-MM-DD") + " "
        + Math.floor(values[1] / 60).toString().padStart(2, '0') + ":"
        + (values[1] % 60).toString().padStart(2, '0') + ":00";

    if (values[1] - values[0] <= 6 * 60)
    {
        graphs.prod.options.scales.xAxes[0].time.unit = 'minute';
        graphs.qa.options.scales.xAxes[0].time.unit = 'minute';
    }
    else
    {
        graphs.prod.options.scales.xAxes[0].time.unit = 'hour';
        graphs.qa.options.scales.xAxes[0].time.unit = 'hour';
    }

    graphs.prod.update();
    graphs.qa.update();
}

$(function ()
{
    $("#slider-range").slider({
        range: true,
        min: 0,
        max: 1440, //24*60
        values: [0, 1440],
        slide: function (event, ui)
        {
            refreshGraph();
        }
    });
    $("#amount").val("$" + $("#slider-range").slider("values", 0) +
        " - $" + $("#slider-range").slider("values", 1));
});

const graphs = {};
window.onload = function ()
{
    let config = {
        type: 'line',
        data: {
            // datasets: [],
        },
        options: {

            responsive: true,
            title: {
                display: true,
                text: 'Wiki PROD timing'
            },
            tooltips: {
                mode: 'index',
                intersect: false,
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                    type: 'time',
                    time: {
                        unit: 'hour'
                    },
                    ticks: {
                        // min:'2020-06-12 10:00:00'
                    }
                }],
                yAxes: [
                    {
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Delay'
                        },
                        type: 'logarithmic',
                        ticks: {
                            min: 0,
                            max: 25,
                            stepSize: 5
                        },
                        position: 'left',
                        id: 'left',
                    },
                    {
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Jobs'
                        },
                        type: 'linear',
                        ticks: {
                            min: 0,
                            max: 30,
                            stepSize: 1
                        },
                        position: 'right',
                        id: 'right',
                    }
                ]
            }
        }
    };

    let configQA = JSON.parse(JSON.stringify(config));
    configQA.options.title.text = "Wiki QA timing";

    const ctx = document.getElementById('speedGraph').getContext('2d');
    const ctxQA = document.getElementById('speedGraphQA').getContext('2d');
    graphs.prod = new Chart(ctx, config);
    graphs.qa = new Chart(ctxQA, configQA);

    makeGraph();

};

export {graphs, makeGraph, getRealTime, refreshGraph};