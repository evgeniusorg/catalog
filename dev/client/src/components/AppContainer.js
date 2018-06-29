import React from 'react'
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
      goods: [
        {
          id: 1,
          title: 'Cat 1',
          img: '',
          description: 'test description. asd asd asd as da sdas da sd a sd as d asd a d asd ads asdasd aasdasda sda sd a sd asd asd a sd as da sd a sd as da d a sd a sd ad a',
          price: 2000,
        },
      ],
      sorting: 'id',
      offset: 0,
      limit: 100,
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
    this.load(this.state.sorting, this.state.limit, this.state.offset);
  }

  //load goods from server
  load(sorting, limit, offset){
    this.setState({request: this.state.request + 1})

    let options = {
      method: 'get',
      json: true,
    }
    fetch(`${CONFIG.URL_API}goods?sorting=${sorting}&limit=${limit}&offset=${offset}`, options).then((response)=>{
      this.catchResponse([...this.state.goods, ...response.goods], response.total)
    },(error)=>{
      this.catchError(error)
    })
  }

  //save new good
  addGood(good){
    this.setState({request: this.state.request + 1})

    let options = {
      method: 'post',
      data:good,
    }
    fetch(`${CONFIG.URL_API}goods/`, options).then((response)=>{
      this.catchResponse([], this.state.total - 1, 'Good was added!')
      this.load(this.state.sorting, this.state.offset, 0)
      this.closeModal()
    },(error)=>{
      this.catchError(error)
    })
  }

  //edit good
  editGood(good){ 
    this.setState({request: this.state.request + 1})

    let options = {
      method: 'put',
      data: good,
    }
    fetch(`${CONFIG.URL_API}goods/${good.id}`, options).then((response)=>{
      this.catchResponse([...this.state.goods].forEach(e => e.id === good.id ? good : e), this.state.total, 'Good was updated!')
      this.closeModal()
    },(error)=>{
      this.catchError(error)
    })
  }

  //delete good
  deleteGood(id){
    this.setState({request: this.state.request + 1})

    let options = {
      method: 'delete',
    }

    fetch(`${CONFIG.URL_API}goods/${id}`, options).then((response)=>{      
      this.catchResponse([...this.state.goods].filter(e => e.id !== id), this.state.total - 1, 'Good was deleted!')
      this.closeModal()
    },(error)=>{
      this.catchError(error)
    })
  }

  //change type of sorting
  changeSorting(sorting){
    this.setState({sorting, offset: 0, goods: []})
    this.load(sorting, this.state.limit, this.state.offset)
  }

  //change limit
  changeLimit(limit){
    this.setState({limit, offset: 0, goods: []})
    this.load(this.state.sorting, limit, this.state.offset)
  }

  //load more goods
  loadMore(){
    let offset = this.state.offset + this.state.limit;
    this.setState({offset});
    this.load(this.state.sorting, this.state.limit, offset);
  }

  //actions after success request
  catchResponse(goods, total, alert){
    this.setState({goods, total, request: this.state.request - 1})
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
    console.log(error)
    this.setState({
      request: this.state.request - 1, 
      alert: {
        type: 'error',
        text: JSON.stringify(error),
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
      {this.state.request > 0 && <PreloaderContainer />}
      {this.state.alert.type && <AlertContainer type={this.state.alert.type} text={this.state.alert.text} />}
      
      <ModalEdit action={this.state.action} good={this.state.editGood} close={this.closeModal.bind(this)} add={this.addGood.bind(this)} edit={this.editGood.bind(this)}/>
      <ModalDelete action={this.state.action} good={this.state.editGood} close={this.closeModal.bind(this)} delete={this.deleteGood.bind(this)}/>
      
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
      <div className="catalog-footer">
        {this.state.total > Object.keys(this.state.goods).length && <button onClick={(e)=>this.loadMore()}>Load more</button>}
      </div>
    </div>
  } 
}
