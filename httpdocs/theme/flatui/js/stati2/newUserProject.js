!(function (d3) {

  $("#d3linelableinline").empty();
  // new memeber project stati
  var parseTime = d3.timeParse("%Y-%m-%d");
  var svgLine = d3.select("#d3linelableinline")
      .append("svg")
      .attr("width", 1200)
      .attr("height", 600);

  var marginLine = {top: 30, right: 50, bottom: 30, left: 30},
      widthLine = +svgLine.attr("width") - marginLine.left - marginLine.right,
      heightLine = +svgLine.attr("height") - marginLine.top - marginLine.bottom,
      labelPadding = 3;

  var g = svgLine.append("g")
      .attr("transform", "translate(" + marginLine.left + "," + marginLine.top + ")");
             
        d3.json("/statistics/new-members-projects-json", function(error, data) {        
        if (error) throw error;
        data = data.data.results;
        
        // format the data
        data.forEach(function(d) {
            d.date = parseTime(d.date);
            d.members = +d.members;
            d.projects = +d.projects;
        });

        data.columns=['date','members','projects'];      
        var series = data.columns.slice(1).map(function(key) {
          return data.map(function(d) {
            return {
              key: key,
              date: d.date,
              value: d[key]
            };
          });
        });


        var xLine = d3.scaleTime()
            .domain([data[0].date, data[data.length - 1].date])
            .range([0, widthLine]);

        var yLine = d3.scaleLinear()
            .domain([0, d3.max(series, function(s) { return d3.max(s, function(d) { return d.value; }); })])
            .range([heightLine, 0]);

        var zLine = d3.scaleOrdinal(d3.schemeCategory10);

        g.append("g")
            .attr("class", "axis axis--x")
            .attr("transform", "translate(0," + heightLine + ")")
            .call(d3.axisBottom(xLine));

        var serie = g.selectAll(".serie")
            .data(series)
          .enter().append("g")
            .attr("class", "serie");

        serie.append("path")
            .attr("class", "line")
            .style("stroke", function(d) { return zLine(d[0].key); })
            .attr("d", d3.line()
                .x(function(d) { return xLine(d.date); })
                .y(function(d) { return yLine(d.value); }));

        var label = serie.selectAll(".label")
            .data(function(d) { return d; })
          .enter().append("g")
            .attr("class", "label")
            .attr("transform", function(d, i) { return "translate(" + xLine(d.date) + "," + yLine(d.value) + ")"; });
            
            label.append("text")
                 .attr("dy", ".35em")
                 .text(function(d) { return d.value; })
               .filter(function(d, i) { return i === data.length - 1; })
              

       const newText = label.selectAll('text');
       const bbox = newText.node().getBBox();

       label.append('rect', 'text')
           .datum(() => bbox)
           .attr('x', d => (d.x - labelPadding))
           .attr('y', d => (d.y - labelPadding))
           .attr('width', d => (d.width + (2 * labelPadding)))
           .attr('height', d => (d.height + (2 * labelPadding)));

           label.append("text")
                .attr("dy", ".35em")
                .text(function(d) { return d.value; })
              .filter(function(d, i) { return i === data.length - 1; })
              .append("tspan")
                .attr("class", "label-key")
                .text(function(d) { return " " + d.key; });

      });


})(d3);