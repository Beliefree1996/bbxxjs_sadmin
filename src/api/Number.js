import request from '@/utils/request'

export function Login(data) {
    return request({
        url: '/login',
        method: 'post',
        data
    })
}

export function userinfo(data) {
    return request({
        url: '/userinfo',
        method: 'post',
        data
    })
}

export function mobileList(data) {
    return request({
        url: '/mobilelist',
        method: 'post',
        data
    })
}

export function userList(data) {
    return request({
        url: '/userlist',
        method: 'post',
        data
    })
}

export function downChange(data) {
    return request({
        url: '/downchange',
        method: 'post',
        data
    })
}

export function showChange(data) {
    return request({
        url: '/showchange',
        method: 'post',
        data
    })
}

export function creckpc(data) {
    return request({
        url: '/creckpc',
        method: 'post',
        data
    })
}

export function lefthf(data) {
    return request({
        url: '/lefthf',
        method: 'post',
        data
    })
}

export function ywuserlist(data) {
    return request({
        url: '/ywuserlist',
        method: 'post',
        data
    })
}

export function hfuserlist(data) {
    return request({
        url: '/hfuserlist',
        method: 'post',
        data
    })
}

export function delNumpc(data) {
    return request({
        url: '/delnumpc',
        method: 'post',
        data
    })
}

export function mobilefp(data) {
    return request({
        url: '/mobilefp',
        method: 'post',
        data
    })
}

export function chargefp(data) {
    return request({
        url: '/chargefp',
        method: 'post',
        data
    })
}

export function countdatalist(data) {
    return request({
        url: '/countdatalist',
        method: 'post',
        data
    })
}

export function exlcountdata(data) {
    return request({
        url: '/exlcountdata',
        method: 'post',
        data
    })
}

export function extoexl(data) {
    return request({
        url: '/extoexl',
        method: 'post',
        data
    })
}