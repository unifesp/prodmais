  #!/bin/bash

  curl -X DELETE "localhost:9200/coletaprod2?pretty"
  curl -X DELETE "localhost:9200/coletaprodcv2?pretty"
  curl -X DELETE "localhost:9200/coletaprodaut2?pretty"
  curl -X DELETE "localhost:9200/unifesp2?pretty"