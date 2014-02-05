class @MasterClass
    constructor:(@name) ->
    _init: ->
        jQuery ->
            if typeof jp.ready=="function"
                jp.ready()
            else
                jp._ready()
        null
    _ready: ->
        null