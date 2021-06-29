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
  
  //d3.json("/backend/index/getdownloadsdaily?numofmonthback="+numofmonthback, function(error, data) {        
    d3.json("/statistics/getdownloadsdaily?numofmonthback="+numofmonthback, function(error, data) {        
    
      if (error) throw error;
      data = data.data.results;    
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




})(d3);