import React from 'react'

export default function AlertContainer(props) {
    return <div className="alert">
        <div className={props.type}>
            {props.text}
        </div>
    </div>
}