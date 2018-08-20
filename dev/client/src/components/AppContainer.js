import React from 'react'
import request from 'browser-request';

import CONFIG from '../config'

import PreloaderContainer from './PreloaderContainer'
import AlertContainer from './AlertContainer'
import ModalEdit from './ModalEditContainer'
import ModalDelete from './ModalDeleteContainer'
import GoodContainer from './GoodContainer'

export class App extends React.Component {
  constructor(props){
    super(props);

    this.state = {
      goods: [],
      sorting: 'id',
      offset: 0,
      lastId:0,
      order: 'desc',
      limit: 20,
      total: 0,
      request: 0,
      action:undefined,
      editGood: undefined,
      alert: {
        type: null,
        text: ''
      },
    }
  }

  //default list
  componentDidMount(){
    this.load(this.state.sorting, this.state.limit, this.state.offset, this.state.order, this.state.lastId);
  }

  //load goods from server
  load(sorting, limit, offset, order, lastId){
    this.setState({request: this.state.request + 1})

    let options = {
      url: `${CONFIG.URL_API}goods?sorting=${sorting}&limit=${limit}&offset=${offset}&order=${order}&lastid=${lastId}`,
      method: 'get',
      json: true,
    }

    request(options, (error, response, body) =>{
      if (error || response.status >= 400) {
        this.catchError(error || body.message || response.statusText);
        return;
      }
      this.catchResponse([...this.state.goods, ...body.goods], body.total)
    })   

  }

  //save new good
  addGood(good){
    this.setState({request: this.state.request + 1})
    let options = {
      url: `${CONFIG.URL_API}goods/`,
      method: 'post',
      json: good,
    }

    request(options, (error, response, body) =>{
      if (error || response.status >= 400) {
        this.catchError(error || body.message || response.statusText);
        return;
      }
      this.closeModal()
      this.catchResponse([], this.state.total + 1, 'Good was added!')
      this.setState({offset: 0, lastId: 0})
      this.load(this.state.sorting, this.state.limit, 0, this.state.order, this.state.lastId)
    })
  }

  //edit good
  editGood(good){ 
    this.setState({request: this.state.request + 1})

    let options = {
      url: `${CONFIG.URL_API}goods/${good.id}`,
      method: 'put',
      body: JSON.stringify(good),
      json: true,
    }
    request(options, (error, response, body) =>{
      if (error || response.status >= 400) {
        this.catchError(error || body.message || response.statusText);
        return;
      }
      this.catchResponse([...this.state.goods].map(e => e.id === good.id ? good : e), this.state.total, 'Good was updated!')
      this.closeModal()
    })
  }

  //delete good
  deleteGood(id){
    this.setState({request: this.state.request + 1})

    let options = {
      url: `${CONFIG.URL_API}goods/${id}`,
      method: 'delete',
      json: true,
    }

    request(options, (error, response, body) =>{
      if (error || response.status >= 400) {
        this.catchError(error || body.message || response.statusText);
        return;
      }
      this.catchResponse([...this.state.goods].filter(e => e.id !== id), this.state.total - 1, 'Good was deleted!')
      this.closeModal()
    })
  }

  //change type of sorting
  changeSorting(sorting){
    this.setState({sorting, offset: 0, goods: [], total: 0, lastId: 0})
    this.load(sorting, this.state.limit, 0, this.state.order, 0)
  }

  //change type of sorting
  changeOrder(order){
    this.setState({order, offset: 0, goods: [], total: 0, lastId: 0})
    this.load(this.state.sorting, this.state.limit, 0, order, 0)
  }

  //change limit
  changeLimit(limit){
    this.setState({limit, offset: 0, goods: [], total: 0, lastId: 0})
    this.load(this.state.sorting, limit, 0, this.state.order, 0)
  }

  //load more goods
  loadMore(){
    this.load(this.state.sorting, this.state.limit, this.state.offset, this.state.order, this.state.lastId);
  }

  //actions after success request
  catchResponse(goods, total, alert){
    let offset = 0;
    let lastId = 0;

    if (goods.length > 0 && this.state.sorting === 'price') {
        offset = goods[goods.length -1].price
        lastId = goods[goods.length -1].id
    }
    if (goods.length > 0 && this.state.sorting === 'id') {
        offset = goods[goods.length -1].id
    }
  
    this.setState({goods, total, request: this.state.request - 1, offset, lastId})
    if (!alert) return

    this.setState({
      alert:{
        type: 'success',
        text: alert
      }
    })
    this.closeAllert()
  }

  //actions after request with error
  catchError(error){
    this.setState({
      request: this.state.request - 1, 
      alert: {
        type: 'error',
        text: error,
      }
    })
    this.closeAllert()
  }

  //show modal window
  showModal(action, id){
    let editGood = {
      title: '',
      price: '',
      description: '',
      img: '',
    }

    if (id) 
      editGood = this.state.goods.find(e => e.id === id)
    this.setState({action, editGood})
  }

  //close modal window
  closeModal(){
    this.setState({action: undefined, editGood: undefined})
  }

  closeAllert(){
    setTimeout(()=>{
      this.setState({alert:{type:null, text:''}})
    }, 5000)
  }

  render() {
    
    return <div className="catalog">
      {this.state.alert.type && <AlertContainer type={this.state.alert.type} text={this.state.alert.text} />}
      
      <ModalEdit action={this.state.action} count={this.state.request} good={this.state.editGood} close={this.closeModal.bind(this)} add={this.addGood.bind(this)} edit={this.editGood.bind(this)}/>
      <ModalDelete action={this.state.action} count={this.state.request} good={this.state.editGood} close={this.closeModal.bind(this)} delete={this.deleteGood.bind(this)}/>
      
      <div className="catalog-header">
        <div className="catalog-header-title">
          <h1>Catalog of goods ({this.state.total})</h1>
        </div>
        <div className="catalog-header-filter">
          <div className="catalog-header-filter-block">
            <span>Sorting by:</span>
            <button className={this.state.sorting === 'id' ? 'active' : ''} onClick={(e)=>this.changeSorting('id')}>ID</button>
            <button className={this.state.sorting === 'price' ? 'active' : ''} onClick={(e)=>this.changeSorting('price')}>Price</button>
          </div>
          <div className="catalog-header-filter-block">
           <span>Order by:</span>
            <button className={this.state.order === 'asc' ? 'active' : ''} onClick={(e)=>this.changeOrder('asc')}>↑</button>
            <button className={this.state.order === 'desc' ? 'active' : ''} onClick={(e)=>this.changeOrder('desc')}>↓</button>
          </div>
          <div className="catalog-header-filter-block">
            <span>Limit:</span>
            <button className={this.state.limit === 20 ? 'active' : ''} onClick={(e)=>this.changeLimit(20)}>20</button>
            <button className={this.state.limit === 50 ? 'active' : ''} onClick={(e)=>this.changeLimit(50)}>50</button>
            <button className={this.state.limit === 100 ? 'active' : ''} onClick={(e)=>this.changeLimit(100)}>100</button>
            </div>

          <div className="catalog-header-filter-block">
            <button onClick={(e)=>{this.showModal('add', null)}}>Add new good</button>
          </div>
        </div>
      </div>
      <div className="catalog-list">
        {this.state.goods.map(good=>{
          return <GoodContainer key={good.id} good={good} showModal={this.showModal.bind(this)} />
        })}
      </div>
      {this.state.request > 0 && !this.state.action && <PreloaderContainer />}
      <div className="catalog-footer">
        {this.state.goods.length > 0 && this.state.total > Object.keys(this.state.goods).length && <button onClick={(e)=>this.loadMore()}>Load more</button>}
      </div>
    </div>
  } 
}
