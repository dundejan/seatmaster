import React, {useEffect, useState} from "react";
import PropTypes from "prop-types";
import Office from "./Office";
import {DndProvider} from "react-dnd";
import {HTML5Backend} from "react-dnd-html5-backend";
import {getCurrentAssignments, getSeats, updateSeatCoords, updateChairRotation} from "../api/api";
import PopupWarning from "../Warnings/PopupWarning";

// Function to get current datetime in the format "YYYY-MM-DDTHH:MM"
const getCurrentDateTimeLocal = () => {
	const now = new Date();
	return new Date(now.getTime() - now.getTimezoneOffset() * 60000)
		.toISOString()
		.slice(0, 16);
};

const OfficeMapApp = ({ officeId, officeName }) => {
	const [chairs, setChairs] = useState([]);
	const [showPopup, setShowPopup] = useState(false);
	const [popupMessage, setPopupMessage] = useState("");
	const [dateTimeParam, setDateTimeParam] = useState(getCurrentDateTimeLocal());
	const [refreshFlag, setRefreshFlag] = useState(false);

	useEffect(() => {
		const fetchSeatsAndAssignments = async () => {
			try {
				const seatData = await getSeats(officeId);
				const chairPromises = seatData.map(async chair => {
					const currentAssignments = await getCurrentAssignments(chair.id, dateTimeParam);
					return { ...chair, currentAssignments };
				});

				const chairsWithAssignments = await Promise.all(chairPromises);
				setChairs(chairsWithAssignments);
				console.log("Chairs with assignments");
				console.log(chairsWithAssignments);
			} catch (error) {
				console.error("Error fetching data", error);
			}
		};

		fetchSeatsAndAssignments();
	}, [officeId, refreshFlag]); // Include refreshFlag here

	const addNewChair = (newChair) => {
		setChairs([...chairs, newChair]);
	}

	const changeChairRotation = async (id) => {
		// Assuming each double click rotates the chair by 90 degrees
		const chairToRotate = chairs.find(chair => chair.id === id);
		console.log(chairToRotate);
		const newRotation = (chairToRotate.rotation + 90) % 360;

		try {
			const response = await updateChairRotation(id, newRotation);

			if (response.redirected) {
				setShowPopup(true);
				setPopupMessage("Seat rotation was not updated. Access denied. You need to have admin rights.");
				return;
			}

			// Update rotation in the local state
			const updatedChairs = chairs.map(chair => chair.id === id ? { ...chair, rotation: newRotation } : chair);
			setChairs(updatedChairs);
		} catch (error) {
			console.error("Error updating coordinates", error);
		}
	};

	const handleDropChair = async (id, coords) => {
		try {
			const response = await updateSeatCoords(id, coords.x, coords.y);

			if (response.redirected) {
				setShowPopup(true);
				setPopupMessage("Seat coordinates were not updated. Access denied. You need to have admin rights.");
				return;
			}

			const updatedChairs = chairs.map(chair => chair.id === id ? { ...chair, coordX: coords.x, coordY: coords.y, rotation: chair.rotation || 0 } : chair);
			setChairs(updatedChairs);
		} catch (error) {
			console.error("Error updating coordinates", error);
		}
	};

	const showPopupMessage = (message) => {
		setShowPopup(true);
		setPopupMessage(message);
	};

	const closePopup = () => {
		setShowPopup(false);
		setPopupMessage("");
	};

	return (
		<DndProvider backend={HTML5Backend}>
			<Office
				onDropChair={handleDropChair}
				officeId={officeId}
				officeName={officeName}
				showPopupMessage={showPopupMessage} // Passing down the function
				chairs={chairs}
				addNewChair={addNewChair}
				dateTimeParam={dateTimeParam}
				setDateTimeParam={setDateTimeParam}
				refreshFlag={refreshFlag}
				setRefreshFlag={setRefreshFlag}
				changeChairRotation={changeChairRotation}
			>
			</Office>
			{showPopup && <PopupWarning message={popupMessage} onClose={closePopup} />}
		</DndProvider>
	);
};

OfficeMapApp.propTypes = {
	officeId: PropTypes.string.isRequired,
	officeName: PropTypes.string.isRequired,
};

export default OfficeMapApp;
