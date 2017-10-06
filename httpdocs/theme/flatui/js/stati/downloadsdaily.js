!(function (d3) {

  let numofmonthback = $( "#numofmonthback option:selected" ).text();
  $("#downloadsdailyTabContainer").empty();
  // Set the dimensions of the canvas / graph
  var margin = {top: 30, right: 20, bottom: 70, left: 50},
      width = 1200 - margin.left - margin.right,
      height = 600 - margin.top - margin.bottom;

  // Parse the date / time
  //var parseDate = d3.timeParse("%Y%m%d");

  // Set the ranges
  //var x = d3.scaleTime().range([0, width]);  

  var x = d3.scaleLinear().range([0, width]);
  var y = d3.scaleLinear().range([height, 0]);

  // Define the line
  var priceline = d3.line() 
      .x(function(d) { return x(d.date); })
      .y(function(d) { return y(d.price); });
      
  // Adds the svg canvas

  var svg = d3.select("#downloadsdailyTabContainer")
      .append("svg")
          .attr("width", width + margin.left + margin.right)
          .attr("height", height + margin.top + margin.bottom)
      .append("g")
          .attr("transform", 
                "translate(" + margin.left + "," + margin.top + ")");


  // Get the data

  d3.json("/backend/index/getdownloadsdaily?numofmonthback="+numofmonthback, function(error, data) {        
      if (error) throw error;
      data = data.results;    
      data.forEach(function(d) {
     //d.date = parseDate(d.date);
     d.date = +d.date;
      d.price = +d.price;
      });

      // Scale the range of the data
      //x.domain(d3.extent(data, function(d) { return d.date; }));
     
     x.domain([d3.min(data, function(d) { return d.date; }), d3.max(data, function(d) { return d.date; })]);
      y.domain([0, d3.max(data, function(d) { return d.price; })]);

      // Nest the entries by symbol
      var dataNest = d3.nest()
          .key(function(d) {return d.symbol;})
          .entries(data);

      // set the colour scale
      var color = d3.scaleOrdinal(d3.schemeCategory10);

      legendSpace = width/dataNest.length; // spacing for the legend

      // Loop through each symbol / key
      dataNest.forEach(function(d,i) { 

          svg.append("path")
              .attr("class", "line")
              .style("stroke", function() { // Add the colours dynamically
                  return d.color = color(d.key); })
              .attr("d", priceline(d.values));

          // Add the Legend
          svg.append("text")
              .attr("x", (legendSpace/2)+i*legendSpace)  // space legend
              .attr("y", height + (margin.bottom/2)+ 5)
              .attr("class", "legend")    // style the legend
              .style("fill", function() { // Add the colours dynamically
                  return d.color = color(d.key); })
              .text(d.key); 

      });

    // Add the X Axis
    svg.append("g")
        .attr("class", "axis")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(x));

    // Add the Y Axis
    svg.append("g")
        .attr("class", "axis")
        .call(d3.axisLeft(y));

  });









  // new memeber project stati
  var parseTime = d3.timeParse("%Y%m%d");
  var svgLine = d3.select("#payoutyear")
      .append("svg")
      .attr("width", 500)
      .attr("height", 250);

  var marginLine = {top: 30, right: 50, bottom: 50, left: 30},
      widthLine = +svgLine.attr("width") - marginLine.left - marginLine.right,
      heightLine = +svgLine.attr("height") - marginLine.top - marginLine.bottom,
      labelPadding = 3;

  var xLine = d3.scaleTime().range([0, widthLine]);
  var g = svgLine.append("g")
      .attr("transform", "translate(" + marginLine.left + "," + marginLine.top + ")");


      d3.json("/backend/index/getdownloadsdaily", function(error, data) {        
        if (error) throw error;
        data = data.results;
        
        // format the data
        data.forEach(function(d) {
            d.date = parseTime(d.yearmonth);
            d.amount = +d.amount;            
        });

        data.columns=['date','amount'];      
        var series = data.columns.slice(1).map(function(key) {
          return data.map(function(d) {
            return {
              key: key,
              date: d.date,
              value: d[key]
            };
          });
        });

        xLine.domain([data[0].date, data[data.length - 1].date]);
    
        var yLine = d3.scaleLinear()
            .domain([d3.min(series, function(s) { return d3.min(s, function(d) { return d.value; }); }), d3.max(series, function(s) { return d3.max(s, function(d) { return d.value; }); })])
            .range([heightLine, 0]);

        var zLine = d3.scaleOrdinal(d3.schemeCategory10);

        g.append("g")
            .attr("class", "axis axis--x")
            .attr("transform", "translate(0," + heightLine + ")")
            .call(d3.axisBottom(xLine).ticks(data.length).tickFormat(d3.timeFormat("%Y-%m")))
            .selectAll("text")  
            .style("text-anchor", "end")
            .attr("dx", "-.8em")
            .attr("dy", ".15em")
            .attr("transform", "rotate(-65)");
            

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