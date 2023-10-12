import React from "react";
import { createRoot } from 'react-dom/client';
import OfficeMapApp from './OfficeMap/OfficeMapApp';

const targetElement = document.getElementById('office-map');
const officeId = targetElement.getAttribute('data-office-id');
const dataTokens = targetElement.getAttribute('data-tokens');

const tokens = JSON.parse(dataTokens);
console.log(tokens);

const root = createRoot(targetElement);

root.render(
	<OfficeMapApp officeId={officeId} />
)