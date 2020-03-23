/***************************************************************************/
/* Boilerplate: Establish the com.anthonyerutledge.objects.Ajax namespace. */
/***************************************************************************/

if (!this.isUndefined(info.anthonyerutledge.objects.Ajax)) {
    throw new error("The info.anthonyerutledge.objects.Ajax class is already defined."); 
}

/*********************************************************************/

/* Ajax extends Webpage */
info.anthonyerutledge.objects.Ajax = function(xhr, method, url, prefer, acceptMediaType, requestContentType, responseContentType) {
    this.HTTP_GET = "GET";
    this.HTTP_POST = "POST";
    this.HTTP_PUT = "PUT";
    this.HTTP_PATCH = "PATCH";
    this.HTTP_DELETE = "DELETE";
    
    this.xhr = xhr;                                  // XmlHttpRequest object
    this.method = method.toUpperCase();              // Examples: 'POST', 'GET'
    this.url = url;                                  // Examples: 'index.php', home/, etc ...
    this.prefer = prefer;                            // Example: respond-async,wait=10,return=representation,handling=strict
    this.acceptMediaType = acceptMediaType;          // Examples: 'application/json', 'application/xml', 'text/html', 'text/plain'
    this.requestContentType = requestContentType;    // Examples: 'application/x-www-form-urlencoded', 'multipart/form-data'
    this.responseContentType = responseContentType;  // Examples: 'application/json; charset=UTF-8', 'text/html; charset=UTF-8'
};

/* Link prototypes. */
info.anthonyerutledge.objects.Ajax.prototype = Object.create(info.anthonyerutledge.objects.Base.prototype, {
    constructor: {
        configurable: true,
        enumerable: true,
        value: info.anthonyerutledge.objects.Ajax,
        writable: true
    }
});

info.anthonyerutledge.objects.Ajax.prototype.setCoreHttpHeaders = function() {
    this.xhr.setRequestHeader("Prefer", this.prefer);
    this.xhr.setRequestHeader("Accept", this.acceptMediaType);
    this.xhr.setRequestHeader("Content-Type", this.requestContentType);
};

info.anthonyerutledge.objects.Ajax.prototype.getRandomNumber = function() {
    return Math.floor((Math.random() * 1000000)).toString(); 
};

info.anthonyerutledge.objects.Ajax.prototype.getPreamble = function() {
    return "ajaxFlag=1&nocache=" + this.getRandomNumber() + "&"; 
};

info.anthonyerutledge.objects.Ajax.prototype.getQueryString = function(valuesObj) {
    var name, encodedName, encodedValue;
    var pairs = [];
    
    if (this.isUndefined(valuesObj)) {
        throw new Error("No data was present to build a query string!");
    }
    
    for (name in valuesObj) {
        if (!this.isRegObjProp(valuesObj, name)) {
            continue;
        }

        encodedName = encodeURIComponent(name).replace(/%20/g, "+");
        encodedValue = encodeURIComponent(valuesObj[name].toString()).replace(/%20/g, "+");
        pairs.push(encodedName + "=" + encodedValue);
    }

    return this.getPreamble() + pairs.join("&");
};

info.anthonyerutledge.objects.Ajax.prototype.addXHRHandler = function(xhr, event, handler) {
    if ("addEventListener" in xhr) {               // If W3C DOM LEVEL 3
        xhr.addEventListener(event, handler, false); 
    } else if ("attachEvent" in xhr) {             // If Microsoft DOM LEVEL 3
        xhr.attachEvent("on" + event, handler);
    } else {                                       // DOM LEVEL 2
        this.setDomLevel2Event(xhr, event, handler);
    }
};

info.anthonyerutledge.objects.Ajax.prototype.send = function() {
    if (this.method === this.HTTP_GET) {
        this.ajaxGet();
    } else if (this.method === this.HTTP_POST) {
        this.ajaxPost();
    } else if (this.method === this.HTTP_PUT) {
        ;
    } else if (this.method === this.HTTP_PATCH) {
        ;
    } else if (this.method === this.HTTP_DELETE) {
        ;
    } else {
        throw new Error("Invalid HTTP Request requested!");
    }
};

/**
 * XMLHttpRequest Level 1 event listener.
 */
//info.anthonyerutledge.objects.Ajax.prototype.xhr1Listener = function(contentType, webpage, destinationId)
//{
//    return function() //This is done so that I can pass arguments to an event listener.
//    {
//        if(this instanceof XMLHttpRequest)
//        {
//            if((this.readyState === 4) && (this.status === 200))
//            {
//                if(this.getResponseHeader('Content-Type') === contentType)  //The Content-Type must match the expected type.
//                {
//                    switch(contentType)
//                    {
//                        case 'text/plain; charset=UTF-8':
//                            webpage.processTextResponse(this.responseText, destinationId);
//                            break;
//                        case 'text/html; charset=UTF-8':
//                            webpage.processHTMLResponse(this.responseText, destinationId);
//                            break;
//                        case 'application/json; charset=UTF-8':
//                            webpage.processJSONResponse(JSON.parse(this.responseText));
//                            break;
//                        case 'text/xml; charset=UTF-8':
//                            webpage.processXMLResponse(this.responseXML, destinationId);
//                            break;
//                    }
//                }
//            }
//        };
//    };
//};

/**
 * XMLHttpRequest Level 2 event listener.
 */
//info.anthonyerutledge.objects.Ajax.prototype.xhr2Listener = function(contentType, webpage, destinationId)
//{
//    return function(e) //This is done so that I can pass arguments to an event listener.
//    {
//        if(this instanceof XMLHttpRequest)
//        {
//            if(this.status === 200)
//            {                
//                if(this.getResponseHeader('Content-Type') === contentType) //The Content-Type must match the expected type.
//                {   
//                    switch(contentType)
//                    {
//                        case 'text/plain; charset=UTF-8':
//                            webpage.processTextResponse(this.responseText, destinationId);
//                            break;
//                        case 'text/html; charset=UTF-8':
//                            webpage.processHTMLResponse(this.responseText, destinationId);
//                            break;
//                        case 'text/xml; charset=UTF-8':
//                            webpage.processXMLResponse(this.responseXML, destinationId);
//                            break;
//                        case 'application/json; charset=UTF-8':
//                            webpage.processJSONResponse(this.response);
//                            break;
//
//                    }
//                }
//            }
//        }
//    };
//};