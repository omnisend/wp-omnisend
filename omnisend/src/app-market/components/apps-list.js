import {
	Button,
	Card,
	CardHeader,
	CardBody,
	CardFooter,
	__experimentalText as Text,
	__experimentalHeading as Heading
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

import '../datastore/index';

import { STORE_NAME } from '../datastore/constants';

const AppMarket = () => {
	const { randomActivity } = useSelect((select) => {
		return {
			randomActivity: select(STORE_NAME).getRandomActivity()
		};
	});

	return (
		<div className="wrap">
			<Card>
				<CardHeader>
					<Heading level={4}>Card With call to get activity</Heading>
				</CardHeader>
				<CardBody>
					<Text>{randomActivity}</Text>
				</CardBody>
				<CardFooter>
					<Text>Isn't it awesome?</Text>
				</CardFooter>
			</Card>
			<br></br>
			<Button
				variant="primary"
				onClick={() => {
					console.log('clicled on the button');
				}}
			>
				Hey, click me!
			</Button>
		</div>
	);
};

export default AppMarket;
