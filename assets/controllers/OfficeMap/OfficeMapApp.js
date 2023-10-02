import React, {Component} from "react";
import PropTypes from "prop-types";
import Office from "./Office";
import Seat from "./Seat";
import {DndProvider} from "react-dnd";
import {HTML5Backend} from "react-dnd-html5-backend";
import {getSeats, updateSeatCoords} from "../api/seats_api";

function getCurrentAssignments(seatId) {
	return fetch(`/ongoing_assignments/seat/${seatId}`, {
		credentials: 'same-origin',
		headers: {
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		}
	})
		.then(response => {
			if (!response.ok) {
				throw new Error('Network response was not ok: ' + response.statusText);
			}
			return response.json();
		})
		.catch(error => {
			console.error('Fetching assignments failed: ', error);
		});
}

export default class OfficeMapApp extends Component {
	constructor(props) {
		super(props);

		this.officeId = props.officeId;

		this.state = {
			chairs: [],
		}

		this.handleDropChair = this.handleDropChair.bind(this);
	}

	componentDidMount() {
		getSeats(this.officeId)
			.then((data) => {
				this.setState({
					chairs: data
				}, this.fetchAssignments);
			});
	}

	fetchAssignments() {
		// Map through the chairs and fetch assignments for each chair
		const chairsWithAssignmentsPromises = this.state.chairs.map(chair =>
			getCurrentAssignments(chair.id).then(currentAssignments => ({...chair, currentAssignments}))
		);

		// Wait for all requests to finish and update state with chairs containing their assignments
		Promise.all(chairsWithAssignmentsPromises)
			.then(chairsWithAssignments => {
				this.setState({ chairs: chairsWithAssignments }, () => {
					console.log("Updated chairs: ", this.state.chairs);
				});
			});
	}

	handleDropChair(id, coords) {
		this.setState(prevState => {
			const updatedChairs = prevState.chairs.map(chair => {
				if (chair.id === id) {
					return {
						...chair,
						coordX: coords.x,
						coordY: coords.y
					};
				}
				return chair;
			});

			return { chairs: updatedChairs };
		});

		console.log(coords.x, coords.y);

		updateSeatCoords(id, coords.x, coords.y)
			.then((data) => {
			console.log("Seat coordinates updated");
			});
	}

	render() {
		return (
			<DndProvider backend={HTML5Backend}>
				<Office
					onDropChair={this.handleDropChair}
					size="400px"
					width="1000px"
					officeId={this.officeId}
				>
					{this.state.chairs.map(chair => (
						<Seat key={chair.id} id={chair.id} left={chair.coordX} top={chair.coordY} currentAssignments={chair.currentAssignments} />
					))}
				</Office>
			</DndProvider>
		);
	}
}

OfficeMapApp.propTypes = {
	officeId: PropTypes.string.isRequired,
};