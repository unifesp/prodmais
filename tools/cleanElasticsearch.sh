  #!/bin/bash

  curl -X DELETE "localhost:9200/prodmais?pretty"
  curl -X DELETE "localhost:9200/prodmaiscv?pretty"
  curl -X DELETE "localhost:9200/prodmaisaut?pretty"