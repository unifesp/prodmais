(function (window, document) {
  "use strict";  /* Wrap code in an IIFE */
  var jQuery, $; // Localize jQuery variables
  function loadScript(url, callback) {
    /* Load script from url and calls callback once it's loaded */
    var scriptTag = document.createElement('script');
    scriptTag.setAttribute("type", "text/javascript");
    scriptTag.setAttribute("src", url);
    if (typeof callback !== "undefined") {
      if (scriptTag.readyState) {
        /* For old versions of IE */
        scriptTag.onreadystatechange = function () {
          if (this.readyState === 'complete' || this.readyState === 'loaded') {
            callback();
          }
        };
      } else {
        scriptTag.onload = callback;
      }
    }
    (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(scriptTag);
  }

  const htmlLegendPlugin = {
    id: 'htmlLegend',
    afterUpdate(chart, args, options) {
      const ul = getOrCreateLegendList(chart, options.containerID);
      // Remove old legend items
      while (ul.firstChild) {
        ul.firstChild.remove();
      }
      options.predictionResponse.predictions.sort((a, b) => (a.prediction > b.prediction ? -1 : 1)).forEach(
        (prediction, index) => {
          if (index < 3) {
            const li = document.createElement('li');
            li.style.marginLeft = '10px';
            // Color box
            const link = document.createElement("a")
            link.href = "https://sdgs.un.org/goals/goal" + prediction.sdg.code
            const boxSpan = document.createElement('img');
            boxSpan.style.height = '25px';
            boxSpan.style.marginRight = '10px';
            boxSpan.style.width = '25px';
            boxSpan.style.verticalAlign = 'middle';
            boxSpan.style.margin = '2px';
            boxSpan.src = 'https://aurora-sdg.labs.vu.nl/assets/img/sdg_icon_' + prediction.sdg.code + '.png'
            link.appendChild(boxSpan)
            // Text
            const textContainer = document.createElement('p');
            textContainer.style.margin = 0;
            textContainer.style.padding = 0;
            textContainer.style.display = 'inline-block';
            textContainer.style.verticalAlign = 'middle';
            textContainer.style.marginLeft = '5px'
            textContainer.innerHTML = 'SDG' + prediction.sdg.code +': ' + prediction.sdg.name + " (" +  (prediction.prediction * 100).toFixed(0) + "%)"
            li.appendChild(link);
            li.appendChild(textContainer);
            ul.appendChild(li);
          }
        });
    }
  };

  const getOrCreateLegendList = (chart, id) => {
    const legendContainer = document.getElementById(id);
    let listContainer = legendContainer.querySelector('ul');
    if (!listContainer) {
      let title = document.createElement('h3');
      title.textContent = 'Top 3 SDG classifications'
      legendContainer.appendChild(title)
      listContainer = document.createElement('ul');
      listContainer.style.margin = 0;
      listContainer.style.padding = 0;
      listContainer.style.listStyleType = 'none';
      legendContainer.appendChild(listContainer);
    }
    let footer = document.createElement("div")
    footer.style.textAlign = 'center'
    let auroraLink = document.createElement("a")
    auroraLink.href = 'https://aurora-universities.eu/'
    auroraLink.target = '_blank'
    let auroraLogo = document.createElement("img")
    auroraLogo.src = 'https://aurora-sdg.labs.vu.nl/assets/img/aurora_logo.png'
    auroraLogo.style.width='100px'
    auroraLogo.style.verticalAlign = 'middle';
    auroraLink.appendChild(auroraLogo)
    const projectLink = document.createElement("a")
    projectLink.href = "https://aurora-sdg.labs.vu.nl?model=" + chart.options.plugins.htmlLegend.predictionResponse.model + "&text=" + encodeURI(chart.options.plugins.htmlLegend.predictionResponse.text)
    projectLink.text = '[Learn more]';
    projectLink.target = '_blank'
    projectLink.style.color = 'lightgreen'
    projectLink.style.textAlign = 'center';
    projectLink.style.marginLeft = '10px'
    projectLink.style.marginRight = '10px'
    projectLink.style.verticalAlign = 'middle';
    let euLink = document.createElement("a")
    euLink.href = 'https://www.erasmusplus.de/'
    euLink.target = '_blank'
    let euLogo = document.createElement("img")
    euLogo.src = 'https://aurora-sdg.labs.vu.nl/resources/EU-co-funded.jpg'
    euLogo.style.width='100px'
    euLogo.style.verticalAlign = 'middle';
    euLink.appendChild(euLogo)
    footer.appendChild(projectLink)
    footer.appendChild(auroraLink)
    footer.appendChild(euLink)
    legendContainer.appendChild(footer)
    return listContainer;
  };

  function main() {
    jQuery(document).ready(function ($) {
      var css_link = $("<link>", {
        type: "text/css",
        rel: "stylesheet",
        href: "https://aurora-sdg.labs.vu.nl/assets/css/widget.css"
      });
      css_link.appendTo('head');
      $('.sdg-wheel').each(function (i, div) {
        let text = div.getAttribute('data-text').replace('\n', '');
        let model = div.getAttribute('data-model');
        let height = div.getAttribute('data-wheel-height')
        if (!model) {
          model = 'aurora-sdg-multi';
        }
        var canvas = document.createElement('canvas')
        console.log(height)
        div.style.maxWidth = height + 'px'
        canvas.height = height;
        canvas.width = height;

        div.appendChild(canvas)
        canvas.id = 'sdg-wheel-canvas-' + i
        var legendDiv = document.createElement('div')
        legendDiv.className = 'sdg-legend'
        legendDiv.id =  'sdg-wheel-legend-' + i
        div.id = 'sdg-wheel-div-' + i
        legendDiv.style.opacity = '0'
        div.appendChild(legendDiv)
        div.onmouseenter = function () {$(this).children("div")[0].style.opacity = 1}
        div.onmouseleave = function () {$(this).children("div")[0].style.opacity = 0}
        let url = 'https://aurora-sdg.labs.vu.nl/classifier/classify/' + model;
        fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({"text": text}),
        })
          .then((response) => {
            return response.json();
          })
          .then((data) => {
            let predictionResponse = data;

            let predictions = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            predictionResponse.predictions.forEach(
              (value, index) => {
                predictions[index] = value.prediction;
              }
            )
            const ctx = canvas.getContext('2d');
            const myChart = new Chart(ctx, {
              type: 'doughnut',
              data: {
                labels: ['SDG 1: No poverty',
                  'SDG 2: Zero hunger',
                  'SDG 3: Good health and well-being',
                  'SDG 4: Quality Education',
                  'SDG 5: Gender equality',
                  'SDG 6: Clean water and sanitation',
                  'SDG 7: Affordable and clean energy',
                  'SDG 8: Decent work and economic growth',
                  'SDG 9: Industry, innovation and infrastructure',
                  'SDG 10: Reduced inequalities',
                  'SDG 11: Sustainable cities and communities',
                  'SDG 12: Responsible consumption and production',
                  'SDG 13: Climate action',
                  'SDG 14: Life below water',
                  'SDG 15: Life in Land',
                  'SDG 16: Peace, Justice and strong institutions',
                  'SDG 17: Partnerships for the goals'],
                datasets: [{
                  label: '# of Votes',
                  data: predictions,
                  backgroundColor: [
                    '#C42231', '#DDA73A', '#4E9E45', '#C31F2D', '#C63A21', '#40A8C8', '#EBBD15', '#A21C44', '#CD5E22', '#C51C75', '#DB8F20', '#BF8D2C', '#407F46', '#1F97D4', '#59BA47', '#136A9F', '#14496B'
                  ],
                  hoverBackgroundColor: [
                    '#C42231', '#DDA73A', '#4E9E45', '#C31F2D', '#C63A21', '#40A8C8', '#EBBD15', '#A21C44', '#CD5E22', '#C51C75', '#DB8F20', '#BF8D2C', '#407F46', '#1F97D4', '#59BA47', '#136A9F', '#14496B'
                  ],
                  borderColor: 'transparent',
                  borderWidth: 1
                }]
              },
              options: {
                plugins: {
                  legend: {
                    display: false
                  },
                  htmlLegend: {
                    // ID of the container to put the legend in
                    containerID: 'sdg-wheel-legend-' + i,
                    predictionResponse: predictionResponse
                  },
                  tooltip: {
                    enabled: false,
                    display: false
                  }
                }
              },
              plugins: [htmlLegendPlugin,
                {
                  afterDraw: chart => {
                    ctx.save();
                    var image = new Image();
                    image.src = 'https://aurora-sdg.labs.vu.nl/resources/SDG_logo.png';
                    const imageHeight = height / 3.5;
                    const imageWidth = imageHeight * 1.5
                    ctx.drawImage(image, chart.width /2 - imageWidth / 2 , chart.height / 2 - imageHeight /2, imageWidth, imageHeight);
                    ctx.restore();
                  }
                }
              ]
            })
          });
      });
    });
  }

  /* Load jQuery */
  loadScript("https://aurora-sdg.labs.vu.nl/resources/jquery.min.js", function () {
    /* load Chart.js */
    loadScript("https://aurora-sdg.labs.vu.nl/resources/chart.min.js", function () {
      $ = jQuery = window.jQuery.noConflict(true);
      main(); /* Execute the main logic of our widget once jQuery is loaded */
    });
  });
}(window, document)); /* end IIFE */
