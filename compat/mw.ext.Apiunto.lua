-- Compatibility for the deployed Apiunto 3.0.0 API. Native fetch stays available.
local Apiunto = {}
local php

function Apiunto.fetch( source, uri, args )
    if type( source ) ~= 'string' or source == '' then error( 'Invalid source', 2 ) end
    if type( uri ) ~= 'string' then error( 'Invalid uri', 2 ) end
    if args ~= nil and type( args ) ~= 'table' then error( 'Invalid args', 2 ) end
    return php.fetch( source, uri, args or {} )
end

function Apiunto.get_raw( uri, args )
    return php.legacy( uri, type( args ) == 'table' and args or {} )
end

local paths = {
    get_ship = 'v2/vehicles/', get_ground_vehicle = 'v2/vehicles/',
    get_manufacturer = 'v2/manufacturers/', get_comm_link_metadata = 'v2/comm-links/',
    get_starsystem = 'v2/starsystems/', get_celestial_object = 'v2/celestial-objects/',
    get_galactapedia = 'v2/galactapedia/', get_weapon_personal = 'v2/weapons/',
    -- The missing slash is retained from the deployed contract, intentionally.
    get_char_armor = 'v2/armor', get_cooler = 'v2/vehicle-items/',
    get_power_plant = 'v2/vehicle-items/', get_quantum_drive = 'v2/vehicle-items/',
    get_shield = 'v2/vehicle-items/'
}
for name, prefix in pairs( paths ) do
    local endpoint = prefix
    Apiunto[name] = function( identifier, args )
        return Apiunto.get_raw( endpoint .. identifier, args )
    end
end

function Apiunto.setupInterface()
    Apiunto.setupInterface = nil
    php = mw_interface
    mw_interface = nil
    mw = mw or {}
    mw.ext = mw.ext or {}
    mw.ext.Apiunto = Apiunto
    package.loaded['mw.ext.Apiunto'] = Apiunto
end
return Apiunto
