import React from 'react'
import '@instructure/canvas-theme'
import AdminHeader from './AdminHeader'
import CoursesPage from './CoursesPage'
import ReportsPage from './ReportsPage'
import SettingsPage from './SettingsPage'
import UsersPage from './UsersPage'
import AccountSelect from './AccountSelect'
import { View } from '@instructure/ui-view'
import Api from '../../Services/Api'
import MessageTray from '../MessageTray'

import { Text } from '@instructure/ui-text'
import { Spinner } from '@instructure/ui-spinner'

class AdminApp extends React.Component {
  constructor(props) {
    super(props)

    this.settings = props.settings
    this.messages = props.messages

    if(this.settings) {
      if(this.settings.account) {
        this.settings.accountId = this.settings.account.id
      }
      if(this.settings.terms) {
        const termIds = Object.keys(this.settings.terms)
        termIds.sort()
        this.settings.termId = termIds.shift()
      }
    }
    

    this.state = {
      courses: {},
      accountId: this.settings.accountId,
      termId: this.settings.termId,
      accountData: [],
      navigation: 'courses',
      modal: null,
      loadingCourses: true,
    }

    this.handleNavigation = this.handleNavigation.bind(this)
    this.clearMessages = this.clearMessages.bind(this)
    this.addMessage = this.addMessage.bind(this)
    this.t = this.t.bind(this)
    this.handleAccountSelect = this.handleAccountSelect.bind(this)
    this.handleTermSelect = this.handleTermSelect.bind(this)
    this.handleCourseUpdate = this.handleCourseUpdate.bind(this)
    this.loadCourses = this.loadCourses.bind(this)

    this.loadCourses(this.settings.accountId, this.settings.termId)
  }

  render() {
    return (
      <View as="div">
        <AdminHeader
          t={this.t}
          settings={this.settings}
          hasNewReport={this.hasNewReport}
          navigation={this.state.navigation}
          handleNavigation={this.handleNavigation}
          handleModal={this.handleModal} />

        <MessageTray messages={this.messages} t={this.t} clearMessages={this.clearMessages} hasNewReport={true} />

        {(['courses', 'reports'].includes(this.state.navigation)) &&
          <AccountSelect
            settings={this.settings}
            accountId={this.state.accountId}
            termId={this.state.termId}
            t={this.t}
            handleAccountSelect={this.handleAccountSelect}
            handleTermSelect={this.handleTermSelect}
            loadCourses={this.loadCourses}
          />
        }

        {(this.state.loadingCourses) &&
          <View as="div" padding="small 0">
            <View as="div" textAlign="center" padding="medium">
              <Spinner variant="inverse" renderTitle={this.t('label.loading')} />
              <Text as="p" weight="light" size="large">{this.t('label.loading')}</Text>
            </View>
          </View>
        }

        {(!this.state.loadingCourses) && ('courses' === this.state.navigation) &&
          <CoursesPage
            settings={this.settings}
            t={this.t}
            accountId={this.state.accountId}
            courses={this.state.courses}
            termId={this.state.termId}
            addMessage={this.addMessage}
            handleCourseUpdate={this.handleCourseUpdate}
          />
        }
        {(!this.state.loadingCourses) && ('reports' === this.state.navigation) &&
          <ReportsPage
            t={this.t}
            settings={this.settings}
            accountId={this.state.accountId}
            termId={this.state.termId}
          />
        }
        {(!this.state.loadingCourses) && ('users' === this.state.navigation) &&
          <UsersPage
            t={this.t}
            settings={this.settings}
            accountId={this.state.accountId}
            termId={this.state.termId}
          />
        }
        {('settings' === this.state.navigation) &&
          <SettingsPage t={this.t}
            settings={this.settings}
            handleNavigation={this.handlenavigation} />
        }
      </View>
    )
  }


  t(key) {
    return (this.settings.labels[key]) ? this.settings.labels[key] : key
  }

  loadCourses(accountId, termId, isMounted) {
    const api = new Api(this.settings)
    api.getAdminCourses(accountId, termId)
      .then((response) => response.json())
      .then((data) => {
        let courses = {}
        if (Array.isArray(data.data)) {
          data.data.forEach(course => {
            courses[course.id] = course
          })
          this.setState({courses})
        }
        
        this.setState({loadingCourses: false})
      })

    if (isMounted) {
      this.setState({loadingCourses: true})
    }
  }

  handleNavigation(navigation) {
    this.setState({ navigation })
  }

  addMessage = (msg) => {
    this.messages.push(msg)
  }

  clearMessages = () => {
    this.messages = [];
  }
  
  handleAccountSelect(accountId) {
    this.setState({accountId})
  }

  handleTermSelect(termId) {
    this.setState({termId})
  }

  handleCourseUpdate(course) {
    let courses = this.state.courses
    courses[course.id] = course
    this.setState({courses})
  }

}

export default AdminApp