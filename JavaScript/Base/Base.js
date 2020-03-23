"use strict";

/*
Base Object --- Holds fundamental code for completing simple tasks.

Establish the info.anthonyerutledge modular namespace, safely.
 
All of the code in this section exists simply to safely establish a
modular, self-contained namespace.
*/
var info;

if (typeof info === "undefined") {
    info = {};
} else if (typeof info !== "object") {
    throw new TypeError("info is not of type object");
}

if (typeof info.anthonyerutledge === "undefined") {
    info.anthonyerutledge = {};
} else if (typeof info.anthonyerutledge !== "object") {
    throw new TypeError("info.anthonyerutledge is not of type object");
}

if (typeof info.anthonyerutledge.objects === "undefined") {
    info.anthonyerutledge.objects = {};
} else if (typeof info.anthonyerutledge.objects !== "object") {
    throw new TypeError("info.anthonyerutledge.objects is not of type object"); 
}

if (typeof info.anthonyerutledge.objects.Base !== "undefined") {
    throw new TypeError("The info.anthonyerutledge.objects.Base object is already defined."); 
}
/******************************************************************************/

/** 
 * Create the Base object so that you can add methods to it's prototype object. 
 */
info.anthonyerutledge.objects.Base = function(node, element, document) {    
    this.node = node;
    this.element = element;
    this.document = document;
};

/**
 * Returns the type of the parameter.
 */
info.anthonyerutledge.objects.Base.prototype.getType = function(value) {
    return typeof value;
};

/**
 * Determine if a value is undefined.
 */
info.anthonyerutledge.objects.Base.prototype.isUndefined = function(value) {
    return (this.getType(value) === "undefined");
};

/**
 * Determine if param is null.
 */
info.anthonyerutledge.objects.Base.prototype.isBool = function(value) {
    return (value === true) || (value === false);
};

/**
 * Determine if param is null.
 */
info.anthonyerutledge.objects.Base.prototype.isNull = function(value) {
    return (value === null);
};

/**
 * Determine if param is a number.
 */
info.anthonyerutledge.objects.Base.prototype.isNumber = function(value) {
    return (this.getType(value) === "number");
};

/**
 * Determine if param is a real number.
 */
info.anthonyerutledge.objects.Base.prototype.isRealNumber = function(value) {
    return (this.isNumber(value) && Number.isFinite(value));
};

/**
 * Determine if param is a string.
 */
info.anthonyerutledge.objects.Base.prototype.isString = function(value) {
    return (this.getType(value) === "string" || value instanceof String);
};

/**
 * Determine if a param is a function.
 */
info.anthonyerutledge.objects.Base.prototype.isFunction = function(value) {
    return (this.getType(value) === "function");
};

/**
 * Determine if param is a Date.
 */
info.anthonyerutledge.objects.Base.prototype.isDate = function(value) {
    return (value instanceof Date);
};

/**
 * Determine if param is an Array object.
 */
info.anthonyerutledge.objects.Base.prototype.isArray = function(value) {
    return (value instanceof Array);
};

/**
 * Determine if param is an object.
 */
info.anthonyerutledge.objects.Base.prototype.isObject = function(value) {
    return (!this.isNull(value) && this.getType(value) === "object");
};

/**
 * Determine if object is empty.
 */
info.anthonyerutledge.objects.Base.prototype.isEmptyObject = function(param) {
     return (this.isObject(param) && (Object.keys(param).length === 0)); 
};

/**
 * Own property test. Cannot be a function.
 */
info.anthonyerutledge.objects.Base.prototype.isRegObjProp = function(containerObj, property) {
    return (containerObj.hasOwnProperty(property) && !this.isFunction(containerObj[property]));
};

/**
 * Determine if param is an Document object.
 */
info.anthonyerutledge.objects.Base.prototype.isDocument = function(node) {
    return (node instanceof Document);
};

/**
 * Determine if param is an Node object.
 */
info.anthonyerutledge.objects.Base.prototype.isNode = function(eventTarget) {
    return (eventTarget instanceof Node);
};

/**
 * Determine if param is an Element object.
 */
info.anthonyerutledge.objects.Base.prototype.isElement = function(node) {
    return (node instanceof Element);
};

/**
 * Determine if param is an HTMLInputElement object.
 */
info.anthonyerutledge.objects.Base.prototype.isInputControl = function(element) {
    return (element instanceof HTMLInputElement);
};

/**
 * Determine if param is an HTMLSelectElement object.
 */
info.anthonyerutledge.objects.Base.prototype.isSelectControl = function(element) {
    return (element instanceof HTMLSelectElement);
};

/**
 * Determine if param is an HTMLTextareaElement object.
 */
info.anthonyerutledge.objects.Base.prototype.isTextareaControl = function(element) {
    return (element instanceof HTMLTextareaElement);
};

/**
 * Determine if param is an HTML control object.
 */
info.anthonyerutledge.objects.Base.prototype.isControl = function(element) {
    return this.isInputControl(element) ||
        this.isSelectControl(element) ||
        this.isTextareaControl(element);
};

/**
 * Determine if element is a radio control.
 */
info.anthonyerutledge.objects.Base.prototype.isRadio = function(element) {
    return (this.isInputControl(element) && element.getAttribute("type") === "radio");
};

/**
 * Determine if param is a checkbox control.
 */
info.anthonyerutledge.objects.Base.prototype.isCheckbox = function(element) {
    return (this.isInputControl(element) && element.getAttribute("type") === "checkbox");
};

/**
 * Determine if and element is checkable.
 */
info.anthonyerutledge.objects.Base.prototype.isCheckable = function(element) {
    return ("checked" in element);
};

/**
 * Determine the kind of event that has fired.
 */
info.anthonyerutledge.objects.Base.prototype.isEvent = function(vslue) {
    return (vslue instanceof Event);
};

/**
 * Determine the kind of HTML tag an element is.
 */
info.anthonyerutledge.objects.Base.prototype.isTag = function(element, tagName) {
    if (!this.isElement(element)) {
        throw new TypeError("The 1st argument must be an instance of an Element.");
    }
    
    if (!this.isString(tagName)) {
        throw new TypeError("The 2nd argument must be a String: " + this.getType(tagName) + " given.");
    }
    
    return (element.tagName === tagName);
};