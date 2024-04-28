var grafSkupen;
var chart15;
var dateInput = document.getElementById("dateInput");
const ctx = document.getElementById("skupenChart").getContext("2d");
const ctx2 = document.getElementById("chart15").getContext("2d");
var selectedDate = dateInput.value;

document.addEventListener("DOMContentLoaded", function () {
  osveziGraf();
});

async function osveziGraf() {
  selectedDate = dateInput.value;
  if(grafSkupen){
    grafSkupen.destroy();
    chart15.destroy();
  }
  drawGrafSkupno();
  drawGrafMoc();
}

async function drawGrafSkupno() {
  console.log("grafSkupno");
  try {
    var url = `/php/skupniPodatki.php?date=${selectedDate}`;
    var response = await fetch(url);
    const data = await response.json();

    grafSkupen = new Chart(ctx, {
      type: "bar",
      data: {
        labels: ["Prevzeta", "Oddana", "Proizv. sončna", "Poraba hiše"],
        datasets: [
          {
            label: "Električna energija v kWh",
            data: [data.prevzeta, data.oddana, data.proiz, data.hisa],
            backgroundColor: [
              "rgba(255, 99, 132, 0.2)",
              "rgba(54, 162, 235, 0.2)",
              "rgba(75, 192, 192, 0.2)",
              "rgba(90, 34, 139, 0.2)",
            ],
            borderColor: [
              "rgba(255, 99, 132, 1)",
              "rgba(54, 162, 235, 1)",
              "rgba(75, 192, 192, 1)",
              "rgba(90, 34, 139, 1)",
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        plugins: {
          legend: {
            labels: {
              boxWidth: 0,
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });

  } catch (error) {
    console.error("Error fetching data:", error);
  }
}


async function drawGrafMoc(){

  var url = `/php/sqlSelect.php?date=${selectedDate}`;
  var response = await fetch(url);
  var datas = await response.json();

  chart15 = new Chart(ctx2, {
    type: "line",
    data: {
      datasets: [
        {
          label: "Prikaz moči v kW v 15minuntih povprečjih",
          data: datas,
        },
      ],
    },
    options: {
      parsing: {
        xAxisKey: "timestamp",
        yAxisKey: "value",
      },
      plugins: {
        legend: {
          labels: {
            boxWidth: 0,
          },
        },
      },
      scales: {
        x: {
          ticks: {
            display: true,
            autoSkip: true,
            maxTicksLimit: 3,
          },
        },
        y: {
          beginAtZero: true,
          max: 8,
        },
      },
    },
  });
}