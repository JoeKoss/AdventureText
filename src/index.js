import React from 'react';
import ReactDOM from 'react-dom';
import Header from './Header';
import About from './About';
import PlayerStats from './PlayerStats';
import TextBox from './TextBox';
import VoteOptions from './VoteOptions';
import './index.css';

ReactDOM.render(
	<div>
		<Header />
		<About />
		<PlayerStats />
		<TextBox />
		<VoteOptions />
	</div>,
	document.getElementById('root')
	);