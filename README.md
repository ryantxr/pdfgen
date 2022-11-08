# pdfgen

With this PDF library, build a PDF document from JSON.
Create named boxes, tables, images and text boxes then
fill them with data by name.

## Installation

Install with composer:

    composer require ryantxr/pdfgen

## Document

`Document` is the primary class. Create a Document and feed it JSON.

## JSON Must be in a specific format

```json
{
    "doc": {
        "defaultFont":{
            "name": "Helvetica",
            "style": "",
            "size": 12
        },
        "lineWidth":"",
        "orientation" : "P", 
        "unit" : "pt", 
        "size" : "A4",
        "shade" : {
            "r" : 0,
            "g" : 0,
            "b" : 0
        }
    },
    "images" : [
        
    ],
    "labels" : [
        {
            "x":0,
            "y":0,
            "value":"_",
            "replace": "_",
            "font": {
                "name": "Helvetica",
                "style": "",
                "size": "12",
                "color":{
                    "red":0,
                    "green":0,
                    "blue":0
                }
            }
        }
    ],
    "lines" : [
        {
            "lineWidth": 1,
            "startX":1,
            "startY":1,
            "endX":1,
            "endY":1,
            "file": "",
            "x": 0,
            "xConstraint": 0,
            "yConstraint": 12
        }
    ],
    "boxes" : [
        {
            "name": "head",
            "x" : 1,
            "y" : 1,
            "sizeX" : 1,
            "sizeY" : 1,
            "justification": "L",
            "font" : {
                "name": "",
                "style": "",
                "size": ""
            }
        }
    ],
    "imageBoxes" : {},
    "tables": {
        "table1": [
            {
                "name": "table_1",
                "font": {
                    "name": "Helvetica",
                    "style": "",
                    "size": 12
                },
                "x": 1, "y": 2,
                "sizeX": 3, "sizeY": 3,
                "dblSpace": false
            }
        ],
        "table2": [
            {
                "name": "table_2",
                "font": {
                    "name": "Helvetica",
                    "style": "",
                    "size": 12
                },
                "x":1, "y":2,
                "sizeX": 3, "sizeY": 3,
                "dblSpace": false
            }
        ]
    },

    "note": "Put a comment here if you want"
}
```
