module.exports = (mysqlAPI, traits) => {
    const stripe = require('stripe')('YOUR KEY HERE');

    async function getPlanDetailsOfCustomer(customer) {
        const subscriptions = await stripe.subscriptions.list({
            customer: customer.id,
        });

        return subscriptions !== null && subscriptions.hasOwnProperty('data') ? 
            subscriptions.data : 
            null;
    }

    async function checkPlanExistsOnStripe(priceId) {
        return await stripe.prices.retrieve(priceId);
    }

    async function checkUpgradeOrDowngrade(planDetails, newPricing) {
        const newPricingPrice = newPricing.unit_amount;
        const oldPricingPrice = planDetails[0].items.data[0].plan.amount;
        if(newPricingPrice != oldPricingPrice) {
            if(newPricingPrice > oldPricingPrice) return 'upgrade';
            if(newPricingPrice < oldPricingPrice) return 'downgrade';
        }

        return 'equal';
    }

    return {
        testScheduling: async function (req, res) {
            try {

                let customerId = 'cus_QDaSlq7zpBEMjI';
                let priceId = 'pro';

                let customer = await stripe.customers.retrieve(customerId);
                if(!customer || customer == null) throw new Error('Customer does not exist on Stripe');

                let planDetails = await getPlanDetailsOfCustomer(customer);
                if(!planDetails || planDetails.length < 1) throw new Error('No plan found for the customer');

                let newPricing = await checkPlanExistsOnStripe(priceId);
                if(!newPricing) throw new Error('Price Id does not exist');

                let direction = await checkUpgradeOrDowngrade(planDetails, newPricing);
                console.log('direction received');
                console.log(direction);

                planDetails = planDetails[0];
                var diffAmount;
                var newPricingPrice = newPricing.unit_amount;
                var oldPricingPrice = planDetails.items.data[0].plan.amount;

                //Calculate how much of the subscription is left
                var currentPeriodEnd = planDetails.current_period_end;
                var now = parseInt(new Date().getTime()/ 1000);

                var diffUnix = currentPeriodEnd - now;
                var diffInDays = parseInt(diffUnix) / 60 / 60 / 24;
                console.log('Days left for expiry');
                console.log(diffInDays);

                var percentageOfSubscriptionLeft = (parseInt(diffInDays)/30) * 100;

                if(direction === 'upgrade') {        
                    diffAmount = parseInt(newPricingPrice) - parseInt(oldPricingPrice);
                    diffAmount = diffAmount * percentageOfSubscriptionLeft / 100;
                    diffAmount = Math.round(diffAmount);
                    console.log('diffAmount calculated');
                    console.log(diffAmount);

                    // const charge = await stripe.charges.create({
                    //     amount: diffAmount,
                    //     currency: 'usd',
                    //     customer: customerId,
                    // });
                
                    // console.log('charge created');
                    // console.log(charge);

                    
                } else if(direction === 'downgrade') {
                    diffAmount = parseInt(oldPricingPrice) - parseInt(newPricingPrice);
                    console.log('Decide if you want to refund the customer');
                    //Decide here whether you want to refund the customer
                } else {
                    throw new Error('Plan pricing is not changing');
                }

                //Call the subscription schedule API 
                const subscriptionSchedule = await stripe.subscriptionSchedules.create({
                    customer: customerId,
                    start_date: currentPeriodEnd,
                    end_behavior: 'cancel',
                    phases: [{
                        items: [{
                            price: priceId,
                            quantity: 1,
                        }],
                        iterations: 12
                    }],
                    
                });

                console.log('subscription schedule api called');
                console.log(subscriptionSchedule);

                return res.json({
                    status: true,
                    message: 'Executed!',
                    subscriptionSchedule: subscriptionSchedule
                })
            } catch (error) {
                return res.json({
                    "status": false,
                    "message": "Something went wrong. If the issue persists, please contact Customer support.",
                    "debug": {
                        "error_message": error.message
                    }
                })
            }
        }
    }
}