import React from 'react'

export default function GoodContainer(props) {
  return <div className="catalog-list-item">
    <div className="catalog-list-item-btns">
      <button onClick={(e)=>props.showModal('edit', props.good.id)}>Edit</button>
      <button onClick={(e)=>props.showModal('delete', props.good.id)}>Delete</button>
    </div>
    <div className="catalog-list-item-img">
      <img alt={props.good.title.substr(0, 15)} src={props.good.img} />
    </div>
    <div className="catalog-list-item-title">{props.good.title}</div>
    <div className="catalog-list-item-description">{props.good.description}</div>
    <div className="catalog-list-item-price">Price: <span>{props.good.price} $</span></div>
  </div>
} 